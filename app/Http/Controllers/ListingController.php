<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Region;
use App\Models\CarBrand;
use Illuminate\Support\Str;


class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Добавляем Request
    {
        // Получаем параметры сортировки
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Белый список для безопасности
        $allowedSorts = ['created_at', 'price', 'views_count', 'title'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        // Получаем объявления
        $listings = Listing::where('status', 'active')
            ->with(['category', 'region', 'media', 'user.favorites'])
            ->orderBy($sortBy, $sortOrder) // Применяем сортировку
            ->paginate(12)
            ->withQueryString(); // Добавляем параметры к ссылкам пагинации

        // Данные для формы фильтров
        $categories = Category::whereNotNull('parent_id')->orderBy('name')->get();
        $regions = Region::where('type', 'city')->orderBy('name')->get();

        // Используем тот же самый шаблон, что и для поиска
        return view('search.index', compact('listings', 'categories', 'regions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::whereNotNull('parent_id')->orderBy('name')->get();
        $regions = Region::where('type', 'city')->orderBy('name')->get();

        // ✅ ИСПРАВЛЕНО: Сортировка по 'name_en'
        $brands = CarBrand::orderBy('name_en')->get();

        return view('listings.create', compact('categories', 'regions', 'brands'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreListingRequest $request)
    {
        $validatedData = $request->validated();

        // --- START: Moderation Logic ---

        // Get the number of already published listings by the user
        $publishedCount = auth()->user()->listings()->where('status', 'active')->count();

        // Determine the status: 'moderation' if the user has less than 5 active listings, otherwise 'active'
        $status = ($publishedCount < 5) ? 'moderation' : 'active';

        // --- END: Moderation Logic ---


        $validatedData['user_id'] = auth()->id();
        $validatedData['slug'] = Str::slug($validatedData['title']) . '-' . uniqid();
        $validatedData['status'] = 'active'; // Use the new status variable instead of hardcoded 'active'
        $validatedData['language'] = 'ru';

        $listing = Listing::create($validatedData);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $listing->addMedia($image)->toMediaCollection('images');
            }
        }
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                // We save each custom field value if it's not empty
                if (!is_null($value)) {
                    $listing->customFieldValues()->create([
                        'field_id' => $fieldId,
                        'value' => $value
                    ]);
                }
            }
        }
        // Redirect with a message that depends on the status
        $message = ($status === 'moderation')
            ? 'Ваше объявление успешно добавлено и отправлено на модерацию!'
            : 'Ваше объявление успешно добавлено!';

        return redirect()->route('listings.show', $listing)
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Listing $listing)
    {
        $this->authorize('update', $listing);

        $categories = Category::whereNotNull('parent_id')->orderBy('name')->get();
        $regions = Region::where('type', 'city')->orderBy('name')->get();

        // ✅ ИСПРАВЛЕНО: Сортировка по 'name_en'
        $brands = CarBrand::orderBy('name_en')->get();

        // Загружаем связи и преобразуем кастомные поля в удобный формат
        $listing->load('customFieldValues');
        $savedCustomFields = $listing->customFieldValues->pluck('value', 'field_id');

        return view('listings.edit', compact('listing', 'categories', 'regions', 'savedCustomFields', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateListingRequest $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        // 1. Обновляем основные данные объявления
        $listing->update($request->validated());

        // 2. Удаляем отмеченные изображения
        if ($request->has('delete_images')) {
            Media::whereIn('id', $request->delete_images)->delete();
        }

        // 3. Добавляем новые изображения
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $listing->addMedia($image)->toMediaCollection('images');
            }
        }

        // 4. Обновляем кастомные поля (удаляем старые, вставляем новые)
        $listing->customFieldValues()->delete();
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                if (!is_null($value)) {
                    $listing->customFieldValues()->create([
                        'field_id' => $fieldId,
                        'value' => $value
                    ]);
                }
            }
        }

        return redirect()->route('dashboard.my-listings')
            ->with('success', 'Объявление успешно обновлено!');
    }

    public function show(Listing $listing)
    {
        $listing->increment('views_count');

        // Загружаем все нужные связи ОДНИМ запросом
        $listing->load([
            'media',
            'user',
            'region',
            'category',
            'customFieldValues.field',
            'reviews.reviewer' // Загружаем отзывы и сразу авторов этих отзывов
        ]);

        return view('listings.show', compact('listing'));
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $listing)
    {
        $this->authorize('delete', $listing);

        $listing->delete();

        return redirect()->route('dashboard.my-listings')
            ->with('success', 'Объявление успешно удалено!');
    }
}
