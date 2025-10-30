<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Category;
use App\Models\Region;
use App\Http\Requests\ListingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query()
            ->with(['category', 'region', 'user'])
            ->active()
            ->latest();

        // Фильтрация по категории
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Фильтрация по региону
        if ($request->has('region')) {
            $query->where('region_id', $request->region);
        }

        // Фильтрация по цене
        if ($request->has('price_from')) {
            $query->where('price', '>=', $request->price_from);
        }
        if ($request->has('price_to')) {
            $query->where('price', '<=', $request->price_to);
        }

        // Поиск по тексту
        if ($request->has('q')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->q}%")
                  ->orWhere('description', 'like', "%{$request->q}%");
            });
        }

        $listings = $query->paginate(20)->withQueryString();

        // Получаем категории и регионы для фильтров
        $categories = Cache::remember('flipp-cache-categories_tree', 3600, function () {
            return Category::tree()->get()->toTree()->map(function ($category) {
                // Проверяем, является ли имя строкой JSON, чтобы избежать двойного декодирования
                if (is_string($category->name) && ($decoded = json_decode($category->name, true)) !== null) {
                    $category->name = $decoded[app()->getLocale()] ?? $decoded['en'] ?? 'Unnamed';
                }

                if ($category->children->isNotEmpty()) {
                    $category->children->transform(function ($child) {
                        if (is_string($child->name) && ($decoded = json_decode($child->name, true)) !== null) {
                            $child->name = $decoded[app()->getLocale()] ?? $decoded['en'] ?? 'Unnamed';
                        }
                        return $child;
                    });
                }

                return $category;
            });
        });

        $regions = Cache::remember('regions_list', 3600, function () {
            return Region::all();
        });

        return view('listings.index', compact('listings', 'categories', 'regions'));
    }

    public function create(Request $request)
    {
        $categories = Category::active()->get();
        $regions = Region::all();

        // Получаем данные с аукциона из session
        $auctionData = null;
        if ($request->has('from_auction') && session()->has('auction_vehicle_data')) {
            $auctionData = session('auction_vehicle_data');
            // НЕ удаляем из session до успешного сохранения
        }

        return view('listings.create', compact('categories', 'regions', 'auctionData'));
    }

    /**
     * ТЗ v2.1: Страница для добавления авто с аукциона
     */
    public function createFromAuction()
    {
        // Проверяем, что пользователь - dealer
        if (!auth()->user()->isDealer() && !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещён. Функция доступна только для дилеров.');
        }

        return view('listings.create-from-auction');
    }

    public function store(ListingRequest $request)
    {
        try {
            DB::beginTransaction();

            $listing = Listing::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'region_id' => $request->region_id,
                'listing_type' => $request->listing_type ?? 'parts', // ТЗ v2.1
                'status' => 'pending' // Изменено с pending на active для тестирования
            ]);

            // ТЗ v2.1: Если это объявление об автомобиле - создаём vehicle_details
            if ($request->listing_type === 'vehicle') {
                $vehicleData = $request->input('vehicle', []);

                $listing->vehicleDetail()->create([
                    'make' => $vehicleData['make'] ?? null,
                    'model' => $vehicleData['model'] ?? null,
                    'year' => $vehicleData['year'] ?? null,
                    'mileage' => $vehicleData['mileage'] ?? null,
                    'body_type' => $vehicleData['body_type'] ?? null,
                    'transmission' => $vehicleData['transmission'] ?? null,
                    'fuel_type' => $vehicleData['fuel_type'] ?? null,
                    'engine_displacement_cc' => $vehicleData['engine_displacement_cc'] ?? null,
                    'exterior_color' => $vehicleData['exterior_color'] ?? null,
                    'is_from_auction' => $vehicleData['is_from_auction'] ?? false,
                    'source_auction_url' => $vehicleData['source_auction_url'] ?? null,
                ]);
            }

            // Сохраняем значения кастомных полей (для обратной совместимости)
            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $field_id => $value) {
                    $listing->fieldValues()->create([
                        'category_field_id' => $field_id,
                        'value' => $value
                    ]);
                }
            }

            // ТЗ v2.1: Обработка фотографий с аукциона (по URL)
            if ($request->has('auction_photos')) {
                foreach ($request->auction_photos as $photoUrl) {
                    if (!empty($photoUrl)) {
                        // Преобразуем относительные ссылки на прокси в абсолютные для addMediaFromUrl
                        if (str_starts_with($photoUrl, '/image-proxy')) {
                            $absolute = rtrim(config('app.url'), '/').$photoUrl;
                            Log::info('Using absolute proxy URL for media import', ['absolute' => $absolute]);
                            $photoUrl = $absolute;
                        }
                        if (filter_var($photoUrl, FILTER_VALIDATE_URL)) {
                            try {
                                $listing->addMediaFromUrl($photoUrl)
                                    ->toMediaCollection('images');

                                Log::info('✅ Successfully added auction photo from URL', ['url' => $photoUrl]);
                            } catch (\Exception $e) {
                                // Логируем ошибку, но не прерываем процесс создания объявления
                                Log::error('❌ Failed to add auction photo from URL', [
                                    'url' => $photoUrl,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                }
            }

            // Обработка изображений, загруженных вручную
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $listing
                        ->addMedia($image)
                        ->withResponsiveImages()
                        ->toMediaCollection('images');
                }
            }

            DB::commit();

            return redirect()
                ->route('listings.show', $listing)
                ->with('success', 'Объявление успешно создано и отправлено на модерацию');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Listing Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании объявления. ' . $e->getMessage()]);
        }
    }

    public function show(Listing $listing)
    {
        // Увеличиваем счетчик просмотров
        $listing->increment('views');

        return view('listings.show', [
            'listing' => $listing->load(['category', 'region', 'user', 'fieldValues.field', 'vehicleDetail']),
            'similar' => $listing->similar()->take(4)->get(),
        ]);
    }
    /**
     * Сохраняет данные аукциона в session и перенаправляет на форму создания
     */
    public function saveAuctionData(Request $request)
    {
        $auctionData = json_decode($request->input('auction_data'), true);

        if (!$auctionData) {
            return redirect()
                ->route('listings.create-from-auction')
                ->with('error', 'Некорректные данные аукциона');
        }

        session(['auction_vehicle_data' => $auctionData]);

        return redirect()
            ->route('listings.create', ['from_auction' => 1]);
    }

    public function edit(Listing $listing)
    {
        $this->authorize('update', $listing);

        $categories = Cache::remember('categories_tree', 3600, function () {
            return Category::tree()->get()->toTree();
        });

        $regions = Cache::remember('regions_list', 3600, function () {
            return Region::all();
        });

        return view('listings.edit', compact('listing', 'categories', 'regions'));
    }

    public function update(ListingRequest $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        try {
            DB::beginTransaction();

            $listing->update([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'region_id' => $request->region_id,
                'listing_type' => $request->listing_type ?? 'parts',
                'status' => 'active'
            ]);

            // ТЗ v2.1: Обновление vehicle_details
            if ($request->listing_type === 'vehicle') {
                $listing->vehicleDetail()->updateOrCreate(
                    ['listing_id' => $listing->id],
                    [
                        'make' => $request->make,
                        'model' => $request->model,
                        'year' => $request->year,
                        'mileage' => $request->mileage,
                        'body_type' => $request->body_type,
                        'transmission' => $request->transmission,
                        'fuel_type' => $request->fuel_type,
                        'engine_displacement_cc' => $request->engine_displacement_cc,
                        'exterior_color' => $request->exterior_color,
                        'is_from_auction' => $request->is_from_auction ?? false,
                        'source_auction_url' => $request->source_auction_url,
                    ]
                );
            }

            // Обновляем значения кастомных полей
            // Сохраняем значения кастомных полей (для обратной совместимости)
            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $field_id => $value) {
                    $listing->fieldValues()->create([
                        'category_field_id' => $field_id,
                        'value' => $value
                    ]);
                }
            }

            // ТЗ v2.1: Обработка фотографий с аукциона (по URL)
            if ($request->has('auction_photos')) {
                foreach ($request->auction_photos as $photoUrl) {
                    if (!empty($photoUrl)) {
                        // Преобразуем относительные ссылки на прокси в абсолютные для addMediaFromUrl
                        if (str_starts_with($photoUrl, '/image-proxy')) {
                            $absolute = rtrim(config('app.url'), '/').$photoUrl;
                            Log::info('Using absolute proxy URL for media import', ['absolute' => $absolute]);
                            $photoUrl = $absolute;
                        }
                        if (filter_var($photoUrl, FILTER_VALIDATE_URL)) {
                            try {
                                $listing->addMediaFromUrl($photoUrl)
                                    ->toMediaCollection('images');

                                Log::info('✅ Successfully added auction photo from URL', ['url' => $photoUrl]);
                            } catch (\Exception $e) {
                                // Логируем ошибку, но не прерываем процесс создания объявления
                                Log::error('❌ Failed to add auction photo from URL', [
                                    'url' => $photoUrl,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                }
            }

            // Обработка изображений, загруженных вручную
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $listing
                        ->addMedia($image)
                        ->withResponsiveImages()
                        ->toMediaCollection('images');
                }
            }

            DB::commit();

            return redirect()
                ->route('listings.show', $listing)
                ->with('success', 'Объявление успешно обновлено и отправлено на модерацию');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении объявления. Попробуйте позже.']);
        }
    }

    public function destroy(Listing $listing)
    {
        $this->authorize('delete', $listing);

        try {
            $listing->delete();
            return redirect()
                ->route('profile.listings')
                ->with('success', 'Объявление успешно удалено');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Не удалось удалить объявление']);
        }
    }
}
