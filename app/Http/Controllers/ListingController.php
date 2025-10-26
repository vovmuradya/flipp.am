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
// ❗️ Убедитесь, что Media импортирована, если используете ее в update
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Добавляем Request
    {
        // ... (Этот метод у вас уже правильный) ...
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['created_at', 'price', 'views_count', 'title'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $listings = Listing::where('status', 'active')
            ->with(['category', 'region', 'media', 'user.favorites'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate(12)
            ->withQueryString();

        // 1. Загружаем только РОДИТЕЛЬСКИЕ категории (scope 'root' из модели)
        // 2. Сортируем по JSON-колонке, используя текущий язык
        $categories = Category::root() // Используем scope 'root' (whereNull('parent_id'))
        ->active()
            ->orderBy('name->' . app()->getLocale()) // Сортируем по JSON
            ->get();

        $regions = Region::where('type', 'city')->orderBy('name')->get();

        return view('search.index', compact('listings', 'categories', 'regions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ✅ ИСПРАВЛЕНО:
        // Загружаем только родительские категории (Транспорт, Недвижимость...)
        // и сортируем по текущему языку
        $categories = Category::whereNull('parent_id')
            ->orderBy('name->' . app()->getLocale())
            ->get();

        $regions = Region::where('type', 'city')->orderBy('name')->get();
        $brands = CarBrand::orderBy('name_en')->get();

        return view('listings.create', compact('categories', 'regions', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreListingRequest $request)
    {
        // ... (Ваш метод store() выглядит правильно) ...
        $validatedData = $request->validated();
        $publishedCount = auth()->user()->listings()->where('status', 'active')->count();
        $status = ($publishedCount < 5) ? 'moderation' : 'active';

        $validatedData['user_id'] = auth()->id();
        $validatedData['slug'] = Str::slug($validatedData['title']) . '-' . uniqid();
        // $validatedData['status'] = 'active'; // Вы используете 'active', а не $status
        $validatedData['status'] = $status; // ❗️ Исправлено: используем переменную $status
        $validatedData['language'] = app()->getLocale(); // ❗️ Улучшено: используем текущую локаль

        // Убедимся, что category_id пришел из hidden input
        // (JavaScript должен был его заполнить)
        $listing = Listing::create($validatedData);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $listing->addMedia($image)->toMediaCollection('images');
            }
        }
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

        // ✅ ИСПРАВЛЕНО:
        // Загружаем только родительские категории (как в create())
        $categories = Category::whereNull('parent_id')
            ->orderBy('name->' . app()->getLocale())
            ->get();

        $regions = Region::where('type', 'city')->orderBy('name')->get();
        $brands = CarBrand::orderBy('name_en')->get();

        $listing->load('customFieldValues');
        $savedCustomFields = $listing->customFieldValues->pluck('value', 'field_id');

        // ❗️ TODO: Для edit.blade.php потребуется сложный JavaScript,
        // чтобы загрузить и ВЫБРАТЬ правильную цепочку категорий (Транспорт -> Автомобили)
        // и полей (Марка -> Модель -> Поколение)

        return view('listings.edit', compact('listing', 'categories', 'regions', 'savedCustomFields', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateListingRequest $request, Listing $listing)
    {
        // ... (Ваш метод update() выглядит правильно) ...
        $this->authorize('update', $listing);
        $listing->update($request->validated());

        if ($request->has('delete_images')) {
            Media::whereIn('id', $request->delete_images)->delete();
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $listing->addMedia($image)->toMediaCollection('images');
            }
        }
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
        // ... (Ваш метод show() выглядит правильно) ...
        $listing->increment('views_count');
        $listing->load([
            'media', 'user', 'region', 'category',
            'customFieldValues.field', 'reviews.reviewer'
        ]);
        return view('listings.show', compact('listing'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $listing)
    {
        // ... (Ваш метод destroy() выглядит правильно) ...
        $this->authorize('delete', $listing);
        $listing->delete();
        return redirect()->route('dashboard.my-listings')
            ->with('success', 'Объявление успешно удалено!');
    }
}
