# Idrom Android Client

Этот модуль содержит стартовую Android-приложение на Kotlin + Jetpack Compose, которое уже умеет запрашивать список объявлений из Laravel-бэкенда (`/api/mobile/listings`). Ниже — инструкция, как собрать и настроить окружение.

## Требования
- Android Studio Iguana или новее (AGP 8.4.1, Gradle 8.7)
- Android SDK 34 (compile/target)
- JDK 17

## Запуск бэкенда
1. Скопируйте `.env.example` → `.env`, пропишите доступ к БД и `APP_URL`.
2. `composer install && php artisan key:generate`.
3. `php artisan migrate --seed` (нужны категории/объявления для списка).
4. `php artisan serve` и убедитесь, что `GET http://127.0.0.1:8000/api/mobile/listings` возвращает JSON.
5. Если запускаете Android-эмулятор, оставьте базовый URL `http://10.0.2.2:8000/` (он прописан в `BuildConfig.API_BASE_URL`). Для реального устройства поменяйте значение в `app/build.gradle.kts`.

## Работа с Android-проектом
1. Откройте папку `mobile-app` в Android Studio.
2. Дождитесь синхронизации Gradle. При первом запуске Android Studio сама скачает Gradle 8.7 и зависимости.
3. Запустите `app` → `Run` (Shift + F10) на эмуляторе или устройстве.

## Архитектура модуля
- `data/remote` — DTO и `Retrofit`-интерфейс `MobileApi`.
- `data/repository` — преобразование DTO в UI-модели и репозиторий `ListingsRepository`.
- `ui/listings` — `ViewModel` + Compose-экран со списком.
- `ui/components` — переиспользуемые карточки.
- `core/ServiceLocator` — простой DI (OkHttp, Retrofit, репозитории).

## Следующие шаги
- Добавить авторизацию по телефону (`/api/mobile/auth/...`) и хранение токена (DataStore + Header).
- Реализовать экраны «Избранное», «Мои объявления», чат.
- Подключить пагинацию (LazyPagingItems или собственная реализация на базе `meta/links`).
- Написать UI-тесты Compose и unit-тесты для `ListingsRepository` (можно использовать `MockWebServer`).
