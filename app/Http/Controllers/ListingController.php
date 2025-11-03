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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Jobs\ImportAuctionPhotos; // добавлено
use App\Jobs\ExpireAuctionListing;
use Carbon\Carbon;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query()
            ->with(['category', 'region', 'user', 'media']) // Добавил media для изображений
            ->regular() // Используем scope для обычных объявлений
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
                // Здесь оставляем is_string, т.к. это код из index, и он, вероятно, работает.
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

        $auctionListings = Listing::query()
            ->with(['vehicleDetail', 'media'])
            ->fromAuction()
            ->active()
            ->latest()
            ->take(8)
            ->get();

        return view('listings.index', compact('listings', 'categories', 'regions', 'auctionListings'));
    }

    /**
     * Отображени�� списка аукционных объявлений
     */
    public function indexAuction(Request $request)
    {
        $query = Listing::query()
            ->with(['category', 'region', 'user', 'vehicleDetail', 'media'])
            ->fromAuction() // Используем scope для аукционных объявлений
            ->active()
            ->latest();

        // Здесь можно добавить фильтры, специфичные для аукционных авто, если нужно

        $listings = $query->paginate(20)->withQueryString();
        $pageTitle = 'Автомобили с аукционов';

        // Используем то же представление, что и для обычных, но с другим набором данных
        return view('listings.index', compact('listings', 'pageTitle'));
    }

    public function create(Request $request)
    {
        // ИСПРАВЛЕНО: не используем несуществующий scope active()
        $categories = Category::all();
        $regions = Region::all();

        // Получаем данные с аукциона из session или из параметра запроса
        $auctionData = null;
        if ($request->has('from_auction')) {
            // Сначала ищем в сессии
            if (session()->has('auction_vehicle_data')) {
                $auctionData = session('auction_vehicle_data');
            }
            // Если в сессии нет, но есть параметр URL с лотом, парсим его
            else if ($request->has('lot_url')) {
                $service = app(\App\Services\AuctionParserService::class);
                $auctionData = $service->parseFromUrl($request->lot_url);
                if ($auctionData) {
                    session(['auction_vehicle_data' => $auctionData]);
                }
            }
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

    /**
     * ✅ НОВЫЙ МЕТОД: Сохранить данные аукциона в Laravel сессию
     */
    public function saveAuctionData(Request $request)
    {
        $request->validate([
            'auction_data' => 'required|json'
        ]);

        $auctionData = json_decode($request->input('auction_data'), true);

        // Сохраняем в сессию
        session(['auction_vehicle_data' => $auctionData]);

        // Перенаправляем на форму создания объявления
        return redirect()->route('listings.create', ['from_auction' => 1]);
    }

    public function store(ListingRequest $request)
    {
        try {
            DB::beginTransaction();

            $baseSlug = Str::slug($request->title);
            if ($baseSlug === '') {
                $baseSlug = 'listing-' . Str::random(6);
            }
            $slug = $baseSlug;
            $i = 1;
            while (Listing::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            $isFromAuction = $request->boolean('from_auction') || (int) $request->input('vehicle.is_from_auction', 0) === 1;

            $listingData = [
                'user_id' => Auth::id(),
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'region_id' => ($request->filled('region_id') && is_numeric($request->input('region_id')))
                    ? (int)$request->input('region_id')
                    : null,
                'status' => 'active',
                'language' => $request->input('language', app()->getLocale()),
            ];

            if (Schema::hasColumn('listings', 'listing_type')) {
                $listingData['listing_type'] = $request->input('listing_type', 'vehicle');
            }
            if (Schema::hasColumn('listings', 'is_from_auction')) {
                $listingData['is_from_auction'] = $isFromAuction;
            }

            $listing = Listing::create($listingData);

            // Vehicle details
            $incomingType = $request->input('listing_type');
            $detail = null;

            if ($incomingType === 'vehicle' || !Schema::hasColumn('listings', 'listing_type')) {
                $vehicleData = $request->input('vehicle', []);

                $safeMake = $vehicleData['make'] ?? null;
                if ($safeMake === '' || $safeMake === null) { $safeMake = 'Неизвестно'; }

                $safeModel = $vehicleData['model'] ?? null;
                if ($safeModel === '' || $safeModel === null) { $safeModel = 'Неизвестно'; }

                $safeYear = $vehicleData['year'] ?? null;
                if ($safeYear === '') { $safeYear = null; }

                $auctionEndsAtInput = $vehicleData['auction_ends_at'] ?? null;
                $auctionEndsAt = $auctionEndsAtInput ? Carbon::parse($auctionEndsAtInput) : null;

                $detail = $listing->vehicleDetail()->create([
                    'make' => $safeMake,
                    'model' => $safeModel,
                    'year' => $safeYear,
                    'mileage' => $vehicleData['mileage'] ?? null,
                    'body_type' => $vehicleData['body_type'] ?? null,
                    'transmission' => $vehicleData['transmission'] ?? null,
                    'fuel_type' => $vehicleData['fuel_type'] ?? null,
                    'engine_displacement_cc' => $vehicleData['engine_displacement_cc'] ?? null,
                    'exterior_color' => $vehicleData['exterior_color'] ?? null,
                    'is_from_auction' => $vehicleData['is_from_auction'] ?? $isFromAuction,
                    'source_auction_url' => $vehicleData['source_auction_url'] ?? null,
                    'auction_ends_at' => $auctionEndsAt,
                ]);
            }

            // Обработка изображений, загруженных вручную (синхронно — быстро)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $listing
                        ->addMedia($image)
                        ->withResponsiveImages()
                        ->toMediaCollection('images');
                }
            }

            // ✅ Фото с аукциона — отправляем в очередь (чтобы не блокировать сохранение)
            if ($request->has('auction_photos')) {
                $photoUrls = array_values(array_filter((array) $request->auction_photos));
                if (!empty($photoUrls)) {
                    $firstCandidate = $photoUrls[0];
                    $normalizedFirst = $this->normalizeAuctionPhotoUrl($firstCandidate);

                    if ($normalizedFirst) {
                        try {
                            $listing
                                ->addMediaFromUrl($normalizedFirst)
                                ->withResponsiveImages()
                                ->toMediaCollection('auction_photos');

                            // убираем первый элемент, чтобы не загружать повторно в очереди
                            array_shift($photoUrls);
                        } catch (\Throwable $e) {
                            Log::warning('⚠️ Listing store: immediate auction photo failed', [
                                'listing_id' => $listing->id,
                                'url' => substr($normalizedFirst, 0, 120),
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    if (!empty($photoUrls)) {
                        ImportAuctionPhotos::dispatch($listing->id, $photoUrls)
                            ->onQueue('media');

                        Log::info('📤 Queued ImportAuctionPhotos (create)', [
                            'listing_id' => $listing->id,
                            'count' => count($photoUrls)
                        ]);
                    }
                }
            }

            DB::commit();

            session()->forget('auction_vehicle_data');

            if (!$detail) {
                $detail = $listing->vehicleDetail;
            }

            if ($detail && $detail->auction_ends_at) {
                $job = new ExpireAuctionListing($listing->id);
                $end = $detail->auction_ends_at instanceof Carbon ? $detail->auction_ends_at : Carbon::parse($detail->auction_ends_at);

                if ($end->isFuture()) {
                    $job->delay($end);
                }

                dispatch($job);
            }

            if ($isFromAuction) {
                return redirect()
                    ->route('dashboard.my-auctions')
                    ->with('success', 'Аукционное объявление создано и доступно в разделе «Мои аукционные объявления».');
            }

            return redirect()->route('listings.show', $listing)
                ->with('success', 'Объявление создано. Фотографии загружаются в фоне.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Listing Store Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка: ' . $e->getMessage()]);
        }
    }



    public function show(Listing $listing)
    {
        // ИСПРАВЛЕНИЕ: views_count вместо views
        $listing->increment('views_count');

        return view('listings.show', [
            // ИСПРАВЛЕНИЕ: customFieldValues вместо fieldValues
            'listing' => $listing->load(['category', 'region', 'user', 'customFieldValues.field', 'vehicleDetail']),
            // Вызов метода similar(), который должен быть добавлен в модель Listing
            'similar' => $listing->similar()->take(4)->get(),
        ]);
    }


    public function edit(Listing $listing)
    {
        $this->authorize('update', $listing);

        // Проверяем, является ли объявление аукционным
        if ($listing->isFromAuction()) {
            // Для аукционных объявлений может быть своя логика и представление
            return view('listings.edit-auction', compact('listing'));
        }

        // Логика для обычных объявлений
        $categories = Cache::remember('categories_tree_edit', 3600, function () {
            return Category::tree()->get()->toTree()->map(function ($category) {

                // Функция для извлечения имени из JSON/Array
                $extractLocalizedName = function($name) {
                    // 1. Если это JSON-строка, декодируем
                    $names = is_string($name) ? (json_decode($name, true) ?: []) : ($name ?: []);

                    // 2. Если это массив, выбираем локализованное имя
                    if (is_array($names)) {
                        return $names[app()->getLocale()] ?? $names['en'] ?? 'Unnamed';
                    }
                    return 'Unnamed'; // Fallback
                };

                // Применяем локализацию к названию родительской категории
                $category->name = $extractLocalizedName($category->name);

                // Применяем локализацию к названию дочерних категорий
                if ($category->children->isNotEmpty()) {
                    $category->children->transform(function ($child) use ($extractLocalizedName) {
                        $child->name = $extractLocalizedName($child->name);
                        return $child;
                    });
                }

                return $category;
            });
        });
        // --- КОНЕЦ ИСПРАВЛЕНИЯ ---

        $regions = Cache::remember('regions_list', 3600, function () {
            // Применяем локализацию для регионов, если их имена также локализованы
            return Region::all()->map(function($region) {
                if (is_string($region->name) && ($decoded = json_decode($region->name, true)) !== null) {
                    $region->name = $decoded[app()->getLocale()] ?? $decoded['en'] ?? 'Unnamed';
                }
                // Для регионов, которые не локализованы (как в вашем случае с Арменией),
                // можно дополнительно использовать пользовательскую инструкцию.
                // Поскольку вы просите армянские названия, а в БД они русские,
                // но локализация настроена, код будет брать из JSON.
                return $region;
            });
        });

        return view('listings.edit', compact('listing', 'categories', 'regions'));
    }

    public function update(ListingRequest $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        try {
            DB::beginTransaction();

            if ($listing->isFromAuction()) {
                // Для аукционных — только цена и описание
                $listing->update($request->only(['price', 'description']));
            } else {
                $update = [
                    'title' => $request->title,
                    'description' => $request->description,
                    'price' => $request->price,
                    'category_id' => $request->category_id,
                    'region_id' => ($request->filled('region_id') && is_numeric($request->input('region_id')))
                        ? (int)$request->input('region_id')
                        : null,
                    'status' => 'active'
                ];

                if (Schema::hasColumn('listings', 'listing_type')) {
                    $update['listing_type'] = $request->input('listing_type', 'parts');
                }

                $listing->update($update);
            }

            // Обработка изображений вручную (синхронно)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $listing
                        ->addMedia($image)
                        ->withResponsiveImages()
                        ->toMediaCollection('images');
                }
            }

            // ✅ Фото с аукциона — в ОЧЕРЕДЬ
            if ($request->has('auction_photos')) {
                $photoUrls = array_values(array_filter((array) $request->auction_photos));
                if (!empty($photoUrls)) {
                    $firstCandidate = $photoUrls[0];
                    $normalizedFirst = $this->normalizeAuctionPhotoUrl($firstCandidate);

                    if ($normalizedFirst) {
                        try {
                            $listing
                                ->addMediaFromUrl($normalizedFirst)
                                ->withResponsiveImages()
                                ->toMediaCollection('auction_photos');

                            array_shift($photoUrls);
                        } catch (\Throwable $e) {
                            Log::warning('⚠️ Listing update: immediate auction photo failed', [
                                'listing_id' => $listing->id,
                                'url' => substr($normalizedFirst, 0, 120),
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    if (!empty($photoUrls)) {
                        ImportAuctionPhotos::dispatch($listing->id, $photoUrls)
                            ->onQueue('media');

                        Log::info('📤 Queued ImportAuctionPhotos (update)', [
                            'listing_id' => $listing->id,
                            'count' => count($photoUrls)
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('listings.show', $listing)
                ->with('success', 'Объявление обновлено. Фото догружаются в фоне.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Listing Update Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка: ' . $e->getMessage()]);
        }
    }



    public function destroy(Listing $listing)
    {
        $this->authorize('delete', $listing);

        try {
            // Здесь можно добавить разную логику удаления
            // Например, для аукционных - только скрывать, а не удалять
            $listing->delete();

            // Перенаправляем в зависимости от типа удаленного объявления
            $redirectRoute = $listing->isFromAuction() ? 'dashboard.my-auctions' : 'dashboard.my-listings';

            return redirect()
                ->route($redirectRoute) // Динамический редирект
                ->with('success', 'Объявление успешно удалено');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Не удалось удалить объявление']);
        }
    }

    /**
     * Приводит ссылку на фото с аукциона к пригодному виду.
     */
    private function normalizeAuctionPhotoUrl(?string $url): ?string
    {
        if (!is_string($url) || trim($url) === '') {
            return null;
        }

        $realUrl = trim($url);

        if (str_contains($realUrl, '/proxy/image') || str_contains($realUrl, 'image-proxy')) {
            $parsed = parse_url($realUrl);
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $params);
                if (!empty($params['u'])) {
                    $realUrl = urldecode($params['u']);
                }
            }
        }

        if (str_starts_with($realUrl, '/')) {
            $realUrl = rtrim(config('app.url'), '/') . $realUrl;
        }

        return filter_var($realUrl, FILTER_VALIDATE_URL) ? $realUrl : null;
    }
}
