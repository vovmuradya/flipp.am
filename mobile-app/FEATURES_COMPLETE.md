# Mobile App - Complete Feature Implementation

## Новый функционал (полный как в веб-версии)

### ✅ Архитектура
- Модульная структура: `lib/models/`, `lib/screens/`, `lib/services/`
- Расширенный API client с поддержкой всех эндпоинтов
- Модели данных для всех сущностей

### ✅ Поиск и фильтры
**Файл:** `lib/screens/search_screen.dart`
- Полнотекстовый поиск по объявлениям
- Расширенные фильтры:
  - Категории
  - Регионы
  - Диапазон цен
  - Год выпуска
  - Пробег
  - Тип кузова, коробка, топливо
  - Состояние (битый/небитый)
  - Только аукционы Copart
- Визуальные чипы активных фильтров
- Интеграция с `/api/mobile/listings` с query параметрами

### ✅ Создание объявлений
**Файл:** `lib/screens/create_listing_screen.dart`
- Форма создания для vehicle/parts
- Выбор категории из справочника
- Автокомплит брендов и моделей авто
- Поля: title, price, description, year, mileage
- Загрузка до 10 фото через `image_picker`
- Валидация и отправка с `multipart/form-data`

### ✅ Импорт из Copart
**Файл:** `lib/screens/import_auction_screen.dart`
- Вставка URL аукциона Copart
- Парсинг через `/api/v1/dealer/listings/fetch-from-url`
- Превью импортированных данных (make, model, year, damage, photos)
- Создание объявления одной кнопкой

### ✅ Мои объявления
**Файл:** `lib/screens/my_listings_screen.dart`
- Список всех объявлений пользователя
- Действия:
  - **Bump** - поднять в топ
  - **Edit** - редактировать (TODO: экран редактирования)
  - **Delete** - удалить
- Pull-to-refresh

### ✅ Мои аукционы
**Файл:** `lib/screens/my_auctions_screen.dart`
- Отдельный список аукционных объявлений
- Отображение damage, operational_status
- Управление (delete)
- Endpoint: `/api/mobile/my/auctions`

### ✅ Отзывы о продавцах
**Файл:** `lib/screens/reviews_screen.dart`
- Просмотр всех отзывов продавца
- Средний рейтинг и количество отзывов
- Добавление отзыва:
  - Рейтинг 1-5 звезд
  - Комментарий (опционально)
- Endpoint: `/api/sellers/{id}/reviews`

### ✅ Настройки уведомлений
**Файл:** `lib/screens/settings_screen.dart`
- Email уведомления:
  - Сообщения
  - Новые объявления
  - Снижение цены
- Push уведомления (те же категории)
- Сохранение на сервер: `/api/mobile/notification-settings`

### ✅ Калькулятор импорта
**Файл:** `lib/screens/calculator_screen.dart`
- Расчет полной стоимости импорта из Copart
- Поля ввода:
  - Auction Price (USD)
  - Year (опционально)
- Результат с детализацией:
  - Copart Fee
  - Delivery
  - Customs Duty
  - VAT
  - Recycling Fee
  - Clearance
  - **Total Cost**
- Endpoint: `/api/copart-calculator/calculate`

## Модели данных

### `lib/models/`
- `category.dart` - Category, CategoryField
- `region.dart` - Region
- `car_brand.dart` - CarBrand, CarModel, CarGeneration
- `review.dart` - Review
- `notification_settings.dart` - NotificationSettings
- `filters/search_filters.dart` - SearchFilters

## API Client

### `lib/api_client.dart`
Новые методы:
```dart
// Справочники
fetchRootCategories()
fetchCategoryChildren(int categoryId)
fetchCategoryFields(int categoryId)
fetchCarBrands()
fetchCarModels(int brandId)
fetchCarGenerations(int modelId)

// Поиск и листинги
searchListings(SearchFilters filters, {int page})
fetchMyListings()
fetchMyAuctions()
updateListing({int id, Map fields, List filePaths})
deleteListing(int id)
bumpListing(int id)

// Аукционы
fetchFromAuctionUrl(String url)

// Отзывы
fetchReviews(int sellerId)
createReview({int sellerId, int rating, String? comment})

// Настройки
fetchNotificationSettings()
updateNotificationSettings(NotificationSettings settings)

// Калькулятор
calculateImport(Map<String, String> params)
```

## Навигация

### Profile Screen
Добавлены кнопки:
- **My Listings** → MyListingsScreen
- **My Auctions** → MyAuctionsScreen  
- **Import Calculator** → CalculatorScreen
- **Notifications** → SettingsScreen

### Add Listing Options
- **Vehicle Listing** → CreateListingScreen (type: vehicle)
- **Parts Listing** → CreateListingScreen (type: parts)
- **Import from Copart** → ImportAuctionScreen
- **Quick Sell** → QuickSellBasicInfoScreen (существующий)

### Search Tab
- Заменен SearchPlaceholderScreen на полноценный SearchScreen
- Кнопка фильтров открывает FilterScreen

## Использование

### Запуск
```bash
cd mobile-app
flutter pub get
flutter run
```

### Для Android эмулятора
API автоматически использует `http://10.0.2.2:8000`

### Для iOS симулятора / физических устройств
Используйте IP адрес вашей машины в сети

## TODO (минорные улучшения)
- [ ] Экран редактирования объявления (Edit)
- [ ] Интеграция Reviews в детали продавца
- [ ] Device management экран
- [ ] Safe Deal / Escrow UI (API готово)
- [ ] Call Masking UI (API готово)
- [ ] Сохраненные поиски
- [ ] Локализация (ru/am/en)

## Итого
✅ Все основные функции веб-версии реализованы в mobile app
✅ API клиент поддерживает все эндпоинты
✅ Модульная архитектура для удобного расширения
✅ UI/UX в едином стиле с существующим дизайном
