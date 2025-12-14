# Мобильное приложение: краткое ТЗ по веб-функционалу

Документ для Codex/разработчиков мобильного клиента. Описывает, что реализовано в веб-версии и должно отразиться в приложении. Бэкенд — Laravel (`/app`), REST API + web роуты. Мобильная папка: `mobile-app/` (Flutter/Compose черновик).

## Навигация и экраны
- **Onboarding/Локаль/Тема** (если нужно) — выбор языка: ru/am/en (в веб есть `locale` переключатель).
- **Аутентификация**: регистрация/логин, подтверждение телефона/email (поля `phone`, `phone_verified_at`). Сохранение токена (Sanctum).
- **Главная/Каталог**: фид объявлений (`/listings`, поиск `/search`), карточки с фото, ценой, валютой, значком Buy Now/ставки, состоянием и основным повреждением. Фильтры: категории, регионы, цена, год, пробег, кузов, коробка, топливо, состояние (битый/небитый), сортировка, только Copart (is_from_auction).
- **Поиск**: полнотекстовый (Meilisearch/Scout), автокомплит категорий/брендов/моделей, сохранение поисков (saved_searches).
- **Карточка объявления**: фото-галерея, цена/валюта, Buy Now, текущая ставка, состояние/повреждение, характеристики авто (год, пробег, кузов, двигатель, цвет), ссылки на аукцион (source_auction_url), локация, описание, продавец, кнопка «показать телефон», избранное, жалоба/сообщение.
- **Создание/редактирование объявления**:
  - Шаг выбора типа: vehicle/parts.
  - Форма с категориями, регионом, заголовком, ценой/валютой, описанием, фото (upload), кастомными полями категории.
  - Блок vehicle_details: make/model/year/mileage/body_type/transmission/fuel_type/engine_displacement_cc/exterior_color + auction flags.
  - Импорт с аукциона Copart: ввод URL, парсинг, префилд полей + фото; обработка ошибок (нет фото/блокировка Copart).
- **Аукционные объявления**: список моих аукционов, авто-истечение по auction_ends_at, возможность обновить ставки (refresh current bid).
- **Избранное**: список, добавление/удаление.
- **Сообщения/Чат**: диалоги по объявлениям (messages), отправка текстов, просмотр истории.
- **Отзывы о продавце**: список и добавление (reviews).
- **Профиль пользователя**: имя, телефон, email, роль (dealer/individual), настройки уведомлений, смена пароля.
- **Профиль дилера**: карточка дилера, оплата тарифа (DealerPayment), лимиты объявлений, bump-интервал.
- **Настройки**: язык, logout, очистка кэша, просмотр политики/условий.

## API/данные (что важно для клиента)
- **Auth**: e-mail/phone + пароль (Sanctum). Токен хранить и прокидывать в заголовке.
- **Каталог/поиск**: `ListingResource` включает price, buy_now_price/currency, current_bid, operational_status, primary_damage, vehicle_details, фотографии (media + auction_photo_urls), region, seller, favorites_count.
- **Фильтры**: категории `/api/categories/root|{id}/children|fields`, регионы, условие is_from_auction, condition (damaged/undamaged), диапазоны (price/year/mileage), сортировка.
- **Справочники авто**: бренды/модели/поколения (`/api/car-brands`, `/api/car-models/{brand}`, `/api/models/{id}/generations`).
- **Аукцион-парсер**: `POST /api/v1/dealer/listings/fetch-from-url` (требует dealer). Возвращает поля авто + фото + auction_ends_at + source_auction_url.
- **Избранное**: toggle, список `/favorites`.
- **Сообщения**: список диалогов, создание сообщения по объявлению (message body + listing_id).
- **Отзывы**: получение/создание для seller.
- **Media upload**: multipart upload на `/listings`/`/listings/{id}` для фото, лимиты на количество и размер (5 MB).
- **Пагинация**: все списки — стандартная Laravel pagination (meta/links).

## Особенности домена
- Объявления имеют `listing_type` (`vehicle`/`parts`); vehicle_details заполняются только для `vehicle`.
- Аукционные поля: `is_from_auction`, `source_auction_url`, `auction_ends_at`, `buy_now_price/currency`, `current_bid_price/currency`, `operational_status`, `primary_damage`, `auction_photo_urls`.
- Фото: spatie medialibrary (`images`, `auction_photos`); если нет фото — placeholder.
- Локализация: ru/am/en; тексты в `lang/`.

## Минимальный roadmap мобильного клиента
1) Авторизация + хранение токена.
2) Каталог с фильтрами + карточка объявления (все поля из ListingResource).
3) Избранное и сообщения.
4) Создание объявления: форма + импорт Copart (для dealer), загрузка фото.
5) Профиль/настройки, локаль.

## Путь к проекту
- Код мобильного старта: `mobile-app/` (Flutter/Compose scaffold).
- Бэкенд + веб: корень репозитория, Laravel. Запуск API: `php artisan serve` (dev), база SQLite по умолчанию. API base для эмулятора: `http://10.0.2.2:8000`.

Используйте этот файл как источник требований для генерации экранов/логики мобильного приложения.

test@example.com

test123456

flutter run -d linux
