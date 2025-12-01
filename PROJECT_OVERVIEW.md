# Обзор проекта flipp-am

Короткая справка по стеку, данным и функционалу на основе текущего кода.

## Стек и языки
- Backend: PHP 8.4, Laravel 12, Composer.
- Frontend: Vite, TailwindCSS, Alpine.js, Axios; UI собирается через `npm run dev/build`.
- Mobile: стартовое Android-приложение на Jetpack Compose (`mobile-app/`), работает с `http://10.0.2.2:8000/` по умолчанию.
- Инфраструктура разработки: Laravel Sail (`compose.yaml`) с MySQL 8, Redis, Meilisearch, Mailpit; базовый образ `docker/8.4`.

## Данные и хранилище
- Основная БД в текущей сборке — SQLite (`database/database.sqlite`); MySQL остаётся опцией в Sail.
- Redis используется для кэша/очередей; Meilisearch (через Laravel Scout) — для полнотекстового поиска и фильтров объявлений.
- Медиа-файлы обслуживаются через `spatie/laravel-medialibrary` (коллекции `images`, `auction_photos` с конверсиями thumb/medium/large).
- Почта — Mailpit в dev-среде.
- По умолчанию индексы поиска можно переключать на Algolia через переменную `ALGOLIA_INDEX`.

## Ключевые доменные сущности
- Listings (`listings`): объявления с типом `listing_type` (`vehicle` / `parts`), статусом, валютой, языком, просмотром/бампом, флагом `is_from_auction`, массивом ссылок `auction_photo_urls`.
- Vehicle details (`vehicle_details`): расширенные атрибуты авто (make, model, year, mileage, body/transmission/fuel/engine/displacement, цвета, VIN, URL аукциона, даты торгов) и флаг `is_from_auction`; связь `Listing -> hasOne VehicleDetail`.
- Таксономия: `categories` + кастомные поля (`category_fields`), регионы (`regions`), бренды/модели/поколения.
- Пользователи (`users`) с ролями (`isDealer`, `isIndividual`, `isAdmin`), лимитами объявлений/фото и интервалом `bump`.
- Дополнительно: избранное (`favorites`), сообщения/чаты (`messages`), отзывы (`reviews`), устройства для пушей (`devices`), сохранённые поиски (`saved_searches`), профиль дилера/оплаты.

## Реализованный функционал (web)
- Публичные страницы: главная/каталог (`/`, `/listings`, `/search`), просмотр объявления `/listings/{id|slug}`, список дилеров и карточка дилера.
- Создание/редактирование объявлений:
  - Выбор типа создания (`/listings/create/choose`), ручное создание/редактирование (`/listings/create`, `/listings/{id}/edit`), черновик (`/listings/draft`).
  - Импорт из аукциона (`/listings/create-from-auction`, `/listings/import-auction`) и внешних ссылок (`/listings/create-from-external`).
  - Сохранение данных аукциона в сессию перед формой (`/listings/save-auction-data`).
  - Отдельные маршруты для аукционных объявлений (`/auction-listings/*`) и обновление времени публикации (`ListingRefreshController`).
- Категории/бренды/модели: AJAX-эндпоинты для дерева категорий, полей категории, брендов, моделей, поколений.
- Поиск: контроллер `SearchController` + Scout/Meilisearch индексация в `Listing::toSearchableArray()`.
- Пользовательские разделы: кабинет (`/dashboard/*`) с моими объявлениями/аукционами, избранным, сообщениями; профиль `/profile`; смена локали `/locale`.
- Коммуникации: отправка сообщений по объявлению, отзывы, избранное (toggle), proxy для картинок (`ImageProxyController`, `ProxyController`, `MediaController`).
- Дилеры и платежи: страница оплаты и webhook (`DealerPaymentController`), редактирование профиля дилера.

## API
- Публичные:
  - `/api/categories/root`, `/api/categories/{category}/children`, `/api/categories/{category}/fields`.
  - `/api/brands`, `/api/brands/{brand}/models`, `/api/models/{model}/generations`.
- Парсер аукционов (dealer, Sanctum): `POST /api/v1/dealer/listings/fetch-from-url` — принимает URL Copart/List.am, возвращает структуру авто или fallback; использует `AuctionParserService`.
- Mobile API (`/api/mobile/*`):
  - Аутентификация: отправка кода по телефону, регистрация, логин, refresh/logout/me.
  - Профиль и уведомления: просмотр/обновление профиля, настройки уведомлений.
  - Устройства: CRUD для пуш-токенов.
  - Личные объявления: список, аукционы, создание/редактирование/удаление, bump.
  - Публичные объявления: список и просмотр.
  - Чаты: список, сообщения по объявлению, отметка прочтения.
  - Избранное: список, добавление/удаление.

## Служебные утилиты и очереди
- Автоматическое обновление cookies Copart через `php artisan copart:refresh-cookies` (использует Puppeteer-скрипт `scraper/fetch-copart-cookies.cjs`, пишет в `COPART_COOKIES` и `storage/logs/copart-cookies.log`); ручное обновление — `copart:update-cookies`.
- Очереди/фон: Laravel queue listener (`php artisan queue:listen`) в dev-скрипте `composer dev`; медиа-конверсии выполняются синхронно (`nonQueued`), поэтому без воркера.
- Прокси для изображений и медиа для обхода CORS/хостинг-ограничений.

## Тестирование и запуск
- Backend тесты: `php artisan test` (Pest/PHPUnit), конфиг в `phpunit.xml`.
- Frontend сборка: `npm run dev` или `npm run build`.
- Локальный стенд: `./vendor/bin/sail up` с сервисами MySQL/Redis/Meilisearch/Mailpit; SQLite работает без Docker.
- Полезные материалы: `TESTING_GUIDE.md`, `STATUS_TZ_v2.1.md`, `TECH_SPEC_v2.1_actual.md`, `PROGRESS_REPORT.md`, `README.md` (Copart cookies, mobile-app).

## Текущее состояние и ограничения
- Основная цель v2.1 — быстрое создание объявления дилером по ссылке аукциона; парсер Copart/List.am готов, но требуется полностью довести форму `listings/create` и вывод расширенных данных на странице объявления (`listings/show`).
- MySQL из исходного ТЗ заменён на SQLite по умолчанию; Meilisearch не обновлён дополнительными полями авто.
- Остальной объём из ТЗ v2.0 (расширенный поиск, модерация, мобильные фичи beyond MVP) вынесен в отдельный backlog.
