<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Category;
use App\Models\Region;
use App\Http\Requests\ListingRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Jobs\ImportAuctionPhotos; // добавлено
use App\Jobs\ExpireAuctionListing;
use Carbon\Carbon;
use App\Services\AuctionParserService;
use App\Support\VehicleCategoryResolver;
use App\Support\VehicleAttributeOptions;
use App\Models\CarBrand;
use App\Support\SearchQueryHelper;
class ListingController extends Controller
{
    private const ALLOWED_AUCTION_DOMAINS = [
        'copart.com',
    ];

    public function index(Request $request)
    {
        $onlyRegular = $request->boolean('only_regular');
        $onlyAuctions = $request->boolean('only_auctions');
        $originFilter = $request->string('origin')->lower();

        if ($originFilter === 'regular') {
            $onlyRegular = true;
            $onlyAuctions = false;
        } elseif (in_array($originFilter, ['auction', 'abroad', 'transit'])) {
            $onlyRegular = false;
            $onlyAuctions = true;
        }

        $query = Listing::query()
            ->with(['category', 'region', 'user', 'media']);

        if ($onlyAuctions) {
            $query->fromAuction()->active();
        } elseif ($onlyRegular) {
            $query->regular()->active();
        } else {
            $query->active();
        }

        $query->latest();

        // ======= 🔍 Умный поиск через Meilisearch с откатом ======= //
        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $ids = [];
            $searchFailed = false;

            try {
                $ids = Listing::search($term)->get()->pluck('id')->toArray();
            } catch (\Throwable $e) {
                $searchFailed = true;
                Log::warning('Scout search failed, using DB fallback', [
                    'term' => $term,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($searchFailed) {
                $this->applySearchFallback($query, $term);
            } elseif (!empty($ids)) {
                $query->whereIn('id', $ids)
                    ->orderByRaw("FIELD(id, " . implode(',', $ids) . ")");
            } else {
                $this->applySearchFallback($query, $term);
            }
        }

        // ===== Фильтры ===== //
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('region')) {
            $query->where('region_id', $request->region);
        }

        if ($request->filled('price_from') && is_numeric($request->price_from)) {
            $query->where('price', '>=', (float) $request->price_from);
        }

        if ($request->filled('price_to') && is_numeric($request->price_to)) {
            $query->where('price', '<=', (float) $request->price_to);
        }

        if ($request->filled('currency')) {
            $query->where('currency', strtoupper($request->currency));
        }

        // ===== фильтры по vehicleDetail ===== //
        $query->when($request->filled('brand'), function ($q) use ($request) {
            $q->whereHas('vehicleDetail', function ($q2) use ($request) {
                $q2->whereRaw('LOWER(make) = ?', [mb_strtolower($request->brand)]);
            });
        });

        $query->when($request->filled('model'), function ($q) use ($request) {
            $q->whereHas('vehicleDetail', function ($q2) use ($request) {
                $q2->whereRaw('LOWER(model) = ?', [mb_strtolower($request->model)]);
            });
        });

        if ($request->filled('year_from') && is_numeric($request->year_from)) {
            $yearFrom = max(1900, min((int)$request->year_from, date('Y') + 1));
            $query->whereHas('vehicleDetail', fn($q) => $q->where('year', '>=', $yearFrom));
        }

        if ($request->filled('year_to') && is_numeric($request->year_to)) {
            $yearTo = max(1900, min((int)$request->year_to, date('Y') + 1));
            $query->whereHas('vehicleDetail', fn($q) => $q->where('year', '<=', $yearTo));
        }

        foreach (['body_type', 'transmission', 'fuel_type'] as $field) {
            if ($request->filled($field)) {
                $query->whereHas('vehicleDetail', fn($q) => $q->where($field, $request->$field));
            }
        }

        if ($request->filled('engine_from') && is_numeric($request->engine_from)) {
            $query->whereHas('vehicleDetail', fn($q) =>
            $q->where('engine_displacement_cc', '>=', (int)$request->engine_from));
        }

        if ($request->filled('engine_to') && is_numeric($request->engine_to)) {
            $query->whereHas('vehicleDetail', fn($q) =>
            $q->where('engine_displacement_cc', '<=', (int)$request->engine_to));
        }

        // ✅ Пагинация
        $listings = $query->paginate(20)->withQueryString();

        // ✅ Кеш Featured
        $featuredListings = Cache::remember('featured_listings', 60, function () {
            return Listing::query()
                ->with(['vehicleDetail', 'media'])
                ->active()
                ->latest()
                ->take(12)
                ->get();
        });

        // ✅ Кеш справочников
        $categories = Cache::remember('flipp-cache-categories_tree', 3600, function () {
            return Category::tree()->get()->toTree()->map(function ($category) {
                if (is_string($category->name) && ($decoded = json_decode($category->name, true))) {
                    $category->name = $decoded[app()->getLocale()] ?? $decoded['en'] ?? 'Unnamed';
                }

                if ($category->children->isNotEmpty()) {
                    $category->children->transform(function ($child) {
                        if (is_string($child->name) && ($decoded = json_decode($child->name, true))) {
                            $child->name = $decoded[app()->getLocale()] ?? $decoded['en'] ?? 'Unnamed';
                        }
                        return $child;
                    });
                }

                return $category;
            });
        });

        $regions = Cache::remember('regions_list', 3600, fn() => Region::all());

        $brands = ($onlyRegular || $onlyAuctions)
            ? CarBrand::query()
                ->orderByRaw('COALESCE(NULLIF(name_ru, \'\'), name_en)')
                ->get(['id', 'name_ru', 'name_en'])
            : collect();

        $currentOrigin = $originFilter ?: ($onlyRegular ? 'regular' : ($onlyAuctions ? 'abroad' : 'regular'));

        return view('listings.index', compact(
            'listings',
            'categories',
            'regions',
            'featuredListings',
            'onlyRegular',
            'onlyAuctions',
            'brands',
            'currentOrigin'
        ));
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
        if ($redirect = $this->ensurePhoneVerified()) {
            return $redirect;
        }

        $defaultVehicleCategoryId = VehicleCategoryResolver::resolve();

        // ИСПРАВЛЕНО: не используем несуществующий scope active()
        $categories = Category::all();
        if ($categories->isEmpty() && $defaultVehicleCategoryId) {
            // Категория могла быть создана на лету при резолве — перечитываем коллекцию
            $categories = Category::all();
        }
        $regions = Region::all();

        // Получаем данные с аукциона строго при ?from_auction=1
        $fromAuctionFlow = $request->boolean('from_auction');
        $auctionData = null;

        if ($fromAuctionFlow && session()->has('auction_vehicle_data')) {
            $auctionData = session('auction_vehicle_data');
        } elseif (!$fromAuctionFlow && session()->has('auction_vehicle_data')) {
            session()->forget('auction_vehicle_data');
        }

        return view('listings.create', compact(
            'categories',
            'regions',
            'auctionData',
            'defaultVehicleCategoryId',
            'fromAuctionFlow'
        ));
    }

    /**
     * Страница выбора типа объявления перед созданием
     */
    public function createChoice()
    {
        if ($redirect = $this->ensurePhoneVerified()) {
            return $redirect;
        }

        return view('listings.create-choice');
    }

    /**
     * ТЗ v2.1: Страница для добавления авто с аукциона
     */
    public function createFromAuction()
    {
        if ($redirect = $this->ensurePhoneVerified()) {
            return $redirect;
        }

        return view('listings.create-from-auction');
    }

    /**
     * ✅ НОВЫЙ МЕТОД: Сохранить данные аукциона в Laravel сессию
     */
    public function saveAuctionData(Request $request)
    {
        if ($redirect = $this->ensurePhoneVerified()) {
            return $redirect;
        }

        $request->validate([
            'auction_data' => 'required|json'
        ]);

        $auctionData = json_decode($request->input('auction_data'), true);

        // Сохраняем в сессию
        session(['auction_vehicle_data' => $auctionData]);

        // Перенаправляем на форму создания объявления
        return redirect()->route('listings.create', ['from_auction' => 1]);
    }

    public function importAuctionListing(Request $request, AuctionParserService $service)
    {
        if ($redirect = $this->ensurePhoneVerified()) {
            return $redirect;
        }

        $validated = $request->validate([
            'auction_url' => 'required|url',
        ]);

        $url = $validated['auction_url'];

        if (!$this->isAllowedAuctionUrl($url)) {
            return back()
                ->withInput()
                ->withErrors([
                    'auction_url' => 'Поддерживаются только ссылки с аукциона Copart.',
                ]);
        }

        try {
            set_time_limit(15);

            $parsed = $service->parseFromUrl($url, aggressive: (bool) config('services.copart.aggressive', false));

        if (!$parsed && $service->wasCopartBlocked()) {
            return back()
                ->withInput()
                ->with('auction_error', 'Copart заблокировал загрузку данных. Обновите переменную COPART_COOKIES (или запустите node scraper/fetch-copart-cookies.cjs) и попробуйте снова.');
        }

        if (!$parsed) {
            $parsed = $this->fallbackAuctionData($url);
        }

        if (!$parsed || empty($parsed['make']) || empty($parsed['model'])) {
            return back()
                ->withInput()
                ->with('auction_error', 'Не удалось найти данные по этому лоту. Проверьте ссылку и попробуйте снова.');
        }

            $vehicle = [
                'make' => $parsed['make'] ?? null,
                'model' => $parsed['model'] ?? null,
                'year' => isset($parsed['year']) && preg_match('/^(19|20)\d{2}$/', (string) $parsed['year']) ? (int) $parsed['year'] : null,
                'mileage' => isset($parsed['mileage']) && is_numeric($parsed['mileage']) ? (int) $parsed['mileage'] : null,
                'exterior_color' => $parsed['exterior_color'] ?? null,
                'transmission' => $parsed['transmission'] ?? 'automatic',
                'fuel_type' => $parsed['fuel_type'] ?? 'gasoline',
                'engine_displacement_cc' => isset($parsed['engine_displacement_cc']) && is_numeric($parsed['engine_displacement_cc']) ? (int) $parsed['engine_displacement_cc'] : null,
                'body_type' => $parsed['body_type'] ?? null,
                'photos' => array_values(array_filter($parsed['photos'] ?? [], fn ($u) => is_string($u) && strlen($u) > 5)),
                'source_auction_url' => $parsed['source_auction_url'] ?? $url,
                'auction_ends_at' => $parsed['auction_ends_at'] ?? null,
                'buy_now_price' => isset($parsed['buy_now_price']) && $parsed['buy_now_price'] !== '' ? (float) $parsed['buy_now_price'] : null,
                'buy_now_currency' => $parsed['buy_now_currency'] ?? null,
                'operational_status' => $parsed['operational_status'] ?? null,
            ];

            $titleParts = [];
            if ($vehicle['year']) {
                $titleParts[] = $vehicle['year'];
            }
            if ($vehicle['make']) {
                $titleParts[] = $vehicle['make'];
            }
            if ($vehicle['model']) {
                $titleParts[] = $vehicle['model'];
            }

            $title = trim(implode(' ', $titleParts));

            $descriptionLines = [
                'Автомобиль с аукциона',
                '',
                'Характеристики:',
                '• Марка: ' . ($vehicle['make'] ?? 'Не указано'),
                '• Модель: ' . ($vehicle['model'] ?? 'Не указано'),
                '• Год: ' . ($vehicle['year'] ?? 'Не указан'),
            ];

            if (!empty($vehicle['mileage'])) {
                $descriptionLines[] = '• Пробег: ' . number_format($vehicle['mileage'], 0, '.', ' ') . ' км';
            }

            if (!empty($vehicle['exterior_color'])) {
                $colorText = VehicleAttributeOptions::colorLabel($vehicle['exterior_color']) ?? $vehicle['exterior_color'];
                $descriptionLines[] = '• Цвет: ' . $colorText;
            }

            if (!empty($vehicle['engine_displacement_cc'])) {
                $descriptionLines[] = '• Объем двигателя: ' . number_format((int) $vehicle['engine_displacement_cc'], 0, '.', ' ') . ' куб. см';
            }

            $categoryId = VehicleCategoryResolver::resolve();
            if (!$categoryId) {
                return back()
                    ->withInput()
                    ->with('auction_error', 'Категории для транспортных объявлений не настроены. Обратитесь к администратору.');
            }

            $payload = [
                'title' => $title,
                'description' => implode("\n", $descriptionLines),
                'price' => $vehicle['buy_now_price'] ?? null,
                'category_id' => $categoryId,
                'auction_url' => $url,
                'vehicle' => $vehicle,
                'photos' => $vehicle['photos'],
            ];

            session(['auction_vehicle_data' => $payload]);

            return redirect()->route('listings.create', ['from_auction' => 1]);
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('auction_error', 'Не удалось загрузить данные с аукциона. Попробуйте ещё раз или заполните форму вручную.');
        }
    }

    private function fallbackAuctionData(string $url): ?array
    {
        $path = parse_url($url, PHP_URL_PATH);
        $slug = is_string($path) ? trim($path, '/') : '';

        $lastSegment = null;
        if ($slug !== '') {
            $segments = explode('/', $slug);
            $lastSegment = end($segments) ?: null;
        }

        $parts = $lastSegment ? array_filter(array_map('trim', explode('-', $lastSegment))) : [];

        $year = null;
        $yearPos = null;
        foreach ($parts as $index => $part) {
            if (preg_match('/^(19|20)\d{2}$/', $part)) {
                $year = (int) $part;
                $yearPos = $index;
                break;
            }
        }

        $makes = [
            'acura','audi','bmw','buick','cadillac','chevrolet','chevy','chrysler','dodge','fiat','ford','gmc','honda','hyundai','infiniti','jaguar','jeep','kia','land','rover','lexus','lincoln','mazda','mercedes','benz','mini','mitsubishi','nissan','porsche','ram','subaru','tesla','toyota','volkswagen','vw','volvo','saab','hummer','pontiac','saturn','scion','suzuki','alfa','romeo','peugeot','renault'
        ];

        $make = null;
        $modelTokens = [];

        if ($yearPos !== null) {
            $after = array_slice($parts, $yearPos + 1);
            $stopWords = ['salvage','clean','title','rebuildable','certificate'];
            $filtered = [];
            foreach ($after as $token) {
                $lower = strtolower($token);
                if (in_array($lower, $stopWords, true)) {
                    continue;
                }
                $filtered[] = $token;
            }

            foreach ($filtered as $idx => $token) {
                if (in_array(strtolower($token), $makes, true)) {
                    $make = ucfirst(strtolower($token));
                    $modelTokens = array_slice($filtered, $idx + 1);
                    break;
                }
            }

            if (!$make && !empty($filtered)) {
                $make = ucfirst(strtolower($filtered[0]));
                $modelTokens = array_slice($filtered, 1);
            }
        }

        $make = $make ? ucfirst(strtolower($make)) : null;
        $model = $modelTokens
            ? ucfirst(implode(' ', array_map(fn ($value) => strtolower($value), $modelTokens)))
            : null;

        if (!$make || !$model) {
            return null;
        }

        return [
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'mileage' => null,
            'exterior_color' => null,
            'transmission' => 'automatic',
            'fuel_type' => 'gasoline',
            'engine_displacement_cc' => null,
            'body_type' => null,
            'photos' => [],
            'source_auction_url' => $url,
            'auction_ends_at' => null,
        ];
    }

    private function isAllowedAuctionUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);

        foreach (self::ALLOWED_AUCTION_DOMAINS as $domain) {
            $domain = strtolower($domain);
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }

    public function store(ListingRequest $request)
    {
        if ($redirect = $this->ensurePhoneVerified()) {
            return $redirect;
        }

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
                'currency' => strtoupper($request->input('currency', 'USD')),
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

                $colorLabel = null;
                if (!empty($vehicleData['exterior_color'])) {
                    $colorLabel = VehicleAttributeOptions::colorLabel($vehicleData['exterior_color']) ?? $vehicleData['exterior_color'];
                    $vehicleData['exterior_color'] = $colorLabel;
                }

                $buyNowPrice = isset($vehicleData['buy_now_price']) && is_numeric($vehicleData['buy_now_price'])
                    ? (float) $vehicleData['buy_now_price']
                    : null;
                if ($buyNowPrice !== null && $buyNowPrice <= 0) {
                    $buyNowPrice = null;
                }

                $buyNowCurrency = $vehicleData['buy_now_currency'] ?? null;
                if (!is_string($buyNowCurrency) || !preg_match('/^[A-Z]{3,5}$/', strtoupper($buyNowCurrency))) {
                    $buyNowCurrency = null;
                } else {
                    $buyNowCurrency = strtoupper($buyNowCurrency);
                }

                if ($buyNowPrice === null) {
                    $buyNowCurrency = null;
                }

                $currentBidPrice = isset($vehicleData['current_bid_price']) && is_numeric($vehicleData['current_bid_price'])
                    ? (float) $vehicleData['current_bid_price']
                    : null;
                if ($currentBidPrice !== null && $currentBidPrice <= 0) {
                    $currentBidPrice = null;
                }

                $currentBidCurrency = $vehicleData['current_bid_currency'] ?? null;
                if (!is_string($currentBidCurrency) || !preg_match('/^[A-Z]{3,5}$/', strtoupper($currentBidCurrency))) {
                    $currentBidCurrency = null;
                } else {
                    $currentBidCurrency = strtoupper($currentBidCurrency);
                }

                if ($currentBidPrice === null) {
                    $currentBidCurrency = null;
                }

                $auctionEndsAtInput = $vehicleData['auction_ends_at'] ?? null;
                $auctionEndsAt = null;

                if ($auctionEndsAtInput) {
                    try {
                        $auctionEndsAt = Carbon::parse($auctionEndsAtInput);
                    } catch (\Throwable $e) {
                        Log::warning('Unable to parse auction end date', [
                            'value' => $auctionEndsAtInput,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $shouldAutoExpire = ($vehicleData['is_from_auction'] ?? $isFromAuction) ? true : false;
                if ($shouldAutoExpire && $auctionEndsAt === null) {
                    $auctionEndsAt = Carbon::now()->addDays((int) config('services.copart.default_lot_ttl_days', 30));
                }

                $detail = $listing->vehicleDetail()->create([
                    'make' => $safeMake,
                    'model' => $safeModel,
                    'year' => $safeYear,
                    'mileage' => $vehicleData['mileage'] ?? null,
                    'body_type' => $vehicleData['body_type'] ?? null,
                    'transmission' => $vehicleData['transmission'] ?? null,
                    'fuel_type' => $vehicleData['fuel_type'] ?? null,
                    'engine_displacement_cc' => $vehicleData['engine_displacement_cc'] ?? null,
                    'exterior_color' => $colorLabel,
                    'is_from_auction' => $vehicleData['is_from_auction'] ?? $isFromAuction,
                    'source_auction_url' => $vehicleData['source_auction_url'] ?? null,
                    'auction_ends_at' => $auctionEndsAt,
                    'buy_now_price' => $buyNowPrice,
                    'buy_now_currency' => $buyNowCurrency,
                    'current_bid_price' => $currentBidPrice,
                    'current_bid_currency' => $currentBidCurrency,
                    'current_bid_fetched_at' => $currentBidPrice ? now() : null,
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

            // ✅ Фото с аукциона — отправляем в очередь (если настроена асинхронная очередь)
            if ($request->has('auction_photos')) {
                $photoUrls = collect((array) $request->auction_photos)
                    ->filter(function ($url) {
                        if (!is_string($url)) {
                            return false;
                        }

                        $decoded = urldecode($url);

                        return !str_contains($decoded, 'placeholder.com')
                            && !str_contains($decoded, 'No+Image');
                    })
                    ->values()
                    ->all();

                    if (!empty($photoUrls)) {
                        if ($detail && Schema::hasColumn('vehicle_details', 'preview_image_url') && empty($detail->preview_image_url)) {
                            $detail->preview_image_url = $photoUrls[0];
                            $detail->save();
                        }

                        if (config('queue.default') === 'sync') {
                            ImportAuctionPhotos::dispatchSync($listing->id, $photoUrls);
                        } else {
                            ImportAuctionPhotos::dispatch($listing->id, $photoUrls);
                        }
                    }
                }

            DB::commit();

            session()->forget('auction_vehicle_data');

            if (!$detail) {
                $detail = $listing->vehicleDetail;
            }

            if ($detail && $detail->auction_ends_at && config('queue.default') !== 'sync') {
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
    public function search(Request $request)
    {
        $q = trim($request->input('q'));

        if (empty($q)) {
            return redirect()->route('listings.index');
        }

        try {
            $results = \App\Models\Listing::search($q)->take(50)->get();

            if ($results->isEmpty()) {
                $results = $this->applySearchFallback(
                    Listing::query()->with(['category', 'region', 'media', 'vehicleDetail']),
                    $q
                )
                    ->latest()
                    ->take(50)
                    ->get();
            }
        } catch (\Throwable $e) {
            Log::warning('Scout quick search failed, using fallback', [
                'term' => $q,
                'error' => $e->getMessage(),
            ]);

            $results = $this->applySearchFallback(
                Listing::query()->with(['category', 'region', 'media', 'vehicleDetail']),
                $q
            )
                ->latest()
                ->take(50)
                ->get();
        }

        return view('listings.index', [
            'listings' => $results,
            'q' => $q,
        ]);
    }
    private function ensurePhoneVerified(): ?RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if (empty($user->phone) || empty($user->phone_verified_at)) {
            $message = __('Добавьте и подтвердите номер телефона, чтобы публиковать объявления.');

            return redirect()
                ->route('profile.edit')
                ->with('error', $message);
        }

        return null;
    }

    private function applySearchFallback(Builder $query, string $term): Builder
    {
        $variants = $this->expandSearchVariants($term);
        if (empty($variants)) {
            return $query;
        }

        return $query->where(function (Builder $outer) use ($variants) {
            foreach ($variants as $variant) {
                $likeTerm = $this->buildSearchLike($variant);

                $outer->orWhere(function (Builder $subQuery) use ($likeTerm) {
                    $subQuery->whereRaw('LOWER(title) LIKE ?', [$likeTerm])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$likeTerm])
                        ->orWhereHas('vehicleDetail', function (Builder $vehicleQuery) use ($likeTerm) {
                            $vehicleQuery
                                ->whereRaw('LOWER(make) LIKE ?', [$likeTerm])
                                ->orWhereRaw('LOWER(model) LIKE ?', [$likeTerm]);
                        });
                });
            }
        });
    }

    private function buildSearchLike(string $term): string
    {
        $normalized = mb_strtolower(trim($term));

        if ($normalized === '') {
            return '%';
        }

        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $normalized);
        $pattern = preg_replace('/\s+/u', '%', $escaped) ?? $escaped;

        return '%' . $pattern . '%';
    }

    private function expandSearchVariants(string $term): array
    {
        $variants = SearchQueryHelper::variants($term);
        if (empty($variants)) {
            $variants = [trim($term)];
        }

        $normalizedTerm = SearchQueryHelper::normalizeToken($term);
        if ($normalizedTerm === '') {
            return array_values(array_filter(array_unique($variants)));
        }

        $brandDictionary = Cache::remember('search_brand_dictionary', 3600, function () {
            return CarBrand::query()
                ->select(['name_en', 'name_ru'])
                ->get()
                ->flatMap(function ($brand) {
                    return array_filter([
                        $brand->name_en,
                        $brand->name_ru,
                    ]);
                })
                ->unique()
                ->values()
                ->all();
        });

        foreach ($brandDictionary as $brandName) {
            $normalizedBrand = SearchQueryHelper::normalizeToken($brandName);
            if ($normalizedBrand === '') {
                continue;
            }

            $distance = levenshtein($normalizedBrand, $normalizedTerm);
            if ($normalizedBrand === $normalizedTerm || $distance <= 1) {
                $variants[] = $brandName;
            }
        }

        return array_values(array_filter(array_unique($variants)));
    }



    public function show(Listing $listing)
    {
        // ИСПРАВЛЕНИЕ: views_count вместо views
        $listing->increment('views_count');

        $listing->load(['category', 'region', 'user', 'customFieldValues.field', 'vehicleDetail', 'media']);

        $vehicleDetail = $listing->vehicleDetail;

        $relatedListings = collect();

        if ($vehicleDetail && $vehicleDetail->make) {
            $relatedListings = Listing::query()
                ->with(['vehicleDetail', 'media', 'region'])
                ->active()
                ->where('id', '!=', $listing->id)
                ->whereHas('vehicleDetail', function ($query) use ($vehicleDetail) {
                    $query->whereRaw('LOWER(make) = ?', [mb_strtolower($vehicleDetail->make)]);

                    if ($vehicleDetail->model) {
                        $query->whereRaw('LOWER(model) = ?', [mb_strtolower($vehicleDetail->model)]);
                    }
                })
                ->take(4)
                ->get();
        }

        if ($relatedListings->count() < 4) {
            $fallback = Listing::query()
                ->with(['vehicleDetail', 'media', 'region'])
                ->active()
                ->where('id', '!=', $listing->id)
                ->where('category_id', $listing->category_id)
                ->take(4)
                ->get();

            $relatedListings = $relatedListings
                ->merge($fallback)
                ->unique('id')
                ->take(4)
                ->values();
        }

        return view('listings.show', [
            'listing' => $listing,
            'relatedListings' => $relatedListings,
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

        // ✅ Фото с аукциона — в очередь, если она асинхронная
        if ($request->has('auction_photos')) {
            $photoUrls = array_values(array_filter((array) $request->auction_photos));
            if (!empty($photoUrls)) {
                if ($listing->vehicleDetail && Schema::hasColumn('vehicle_details', 'preview_image_url') && empty($listing->vehicleDetail->preview_image_url)) {
                    $listing->vehicleDetail->update([
                        'preview_image_url' => $photoUrls[0],
                    ]);
                }

                if (config('queue.default') !== 'sync') {
                    ImportAuctionPhotos::dispatchAfterResponse($listing->id, $photoUrls);
                } else {
                    Log::info('⚠️ ImportAuctionPhotos skipped on update (queue driver sync)', [
                        'listing_id' => $listing->id,
                        'count' => count($photoUrls),
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
