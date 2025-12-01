# План внедрения idromTZ (Marketplace 3.0)

Источник: `idromTZ.pdf` (3 страницы, стратегическое ТЗ).

## Краткая сверка (что есть / чего нет)
- Алгоритмическая цена IMV, Out-the-Door Price, бейджи Great/Fair/Overpriced, Seller Score — нет.
- Trust & Safety: call masking, эскроу (Safe Deal), депозиты — нет.
- AI/UX: 360°/AI enhancement, авто-дефектовка, AI-автозаполнение/классификация — нет.
- SEO: programmatic SEO лендинги, schema.org VehicleListing — нет.
- Конверсия: персонализированные лендинги/CTA, соц-доказательства — нет.
- Логистика: расчёт доставки через API — нет.
- BI: дашборды дилера/админ, A/B-тестирование — нет.
- DevOps/архитектура: PostgreSQL+JSONB, Kafka+CDC→Elastic/OpenSearch, шардинг — нет (сейчас Laravel 12, SQLite/MySQL опционально, Scout+Meilisearch/Algolia).
- Уже есть, но не в ТЗ: парсер Copart/List.am, автообновление cookies, Stripe для дилеров, текущий фронт Blade/Alpine/Tailwind, starter Android (Compose).

## Фазовый план (предлагаемый порядок)
1) **Базовая архитектура и данные**
   - Перейти на PostgreSQL+JSONB для гибких атрибутов; подготовить миграции/ORM-слой.
   - Ввести Out-the-Door Price, хранение владельцев, комплектации, региона для IMV.
   - Подготовить события/шину (pub/sub) для CDC → поисковый индекс (Elastic/OpenSearch).
2) **Алгоритмическая цена и доверие**
   - MVP IMV-модели (бенчмарки по данным внутренних листингов + публичные справочники), бейджи сделок.
   - Антианома́лии цен (>2–3σ), флаг проверки.
   - Seller Score: полнота профиля/ответов/отзывов/активности; включить в ранжирование.
3) **Платёжная безопасность**
   - Safe Deal/эскроу: выбор провайдера, API-интеграция, статусы блокировки/освобождения средств.
   - Депозиты бронирования (возвратные), согласование с Safe Deal.
4) **Коммуникации и приватность**
   - Call masking с прокси-номерами, sticky-сессии и аналитикой звонков; опциональная запись по согласию.
5) **Иммерсивный UX и AI**
   - Интеграция 360°/AI enhancement SDK (Impel/Spyne/Glo3D) в веб/мобайл.
   - Авто-дефектовка по фото (FocalX/Pave) для модерации/сертификации.
   - AI-автозаполнение: распознавание модели/комплектации по фото, генерация описания из VIN/характеристик.
6) **SEO и конверсия**
   - Programmatic SEO: генерация SEO-лендов по комбинациям фильтров; уникальный контент/FAQ.
   - Schema.org VehicleListing + Rich Snippets; динамические лендинги/CTA; элементы доверия (просмотры/продажи).
7) **Логистика**
   - Расчёт доставки через uShip-подобные API; отображение сроков/стоимости в карточке.
8) **BI и админка**
   - Дашборды дилера (показы/клики/CTR/звонки/расходы/ROI).
   - Админ BI, A/B тестирование (CTA, карточки, лендинги).
9) **DevOps и наблюдаемость**
   - CI/CD, staging→prod, rollback.
   - Мониторинг/алерты: Grafana/Prometheus; логирование ELK/Sentry.
   - Шардинг объявлений по регионам (после PostgreSQL/Elastic).

## Лог прогресса
- [x] 2024-11-30: Создан план `IDROM_TZ_PLAN.md`, зафиксированы разрывы и порядок фаз.
- [x] 2024-11-30: Добавлены поля для IMV/Out-the-Door/бейджей/аномалий и seller_score (миграция), создан черновой `App\Services\Pricing\ImvService` (эвристика-заглушка).
- [x] 2024-11-30: Прогнаны миграции (`php artisan migrate`).
- [x] 2024-11-30: Интегрирован расчёт IMV/бейджей/аномалий в создание/обновление объявлений (`ListingController@store/update`), добавлена поддержка `out_the_door_price` в валидацию.
- [x] 2024-11-30: Добавлен черновой `SellerScoreService`, расчёт seller_score при создании/обновлении объявлений.
- [x] 2024-11-30: Вывод бейджей/OTD в карточках, странице объявления, избранном, моих объявлениях и моих аукционах.
- [x] 2024-11-30: Добавлен каркас `ImvModel` с фичами (год, пробег, регион), интегрирован в `ImvService` как шаг к реальной модели IMV.
- [x] 2024-11-30: Прокинули seller_score и ценовые поля в индекс Scout (toSearchableArray).
- [x] 2024-11-30: Каркас Safe Deal: `EscrowService`, API `/api/mobile/escrow/create`, KYC-поля у пользователя (kyc_status, kyc_verified_at).
- [x] 2024-11-30: Добавлен JSON-LD Schema.org Vehicle на странице объявления (`components/seo/vehicle-jsonld` + @push('head')).
- [x] 2024-11-30: Создана таблица `escrow_transactions`, модель и CRUD-заглушки: сохранение hold через `EscrowService`, webhook для обновления статуса.
- [x] 2024-11-30: Каркас Call Masking: таблица `call_sessions`, модель, сервис `CallMaskingService`, API `/api/mobile/calls/start`.
- [x] 2024-11-30: Programmatic SEO: роут `/pseo/{path?}`, контроллер `ProgrammaticSeoController`, вьюха `pseo/landing` с фильтрами по сегментам (brand/model/fuel/body/price/year) и выдачей объявлений.
- [x] 2024-11-30: Sitemap `/sitemap.xml` (последние объявления + pSEO комбинации), вьюха `sitemap/xml`.
- [x] 2024-11-30: pSEO ленд: canonical/title/description + FAQ-блок на странице `pseo/landing`.
- [x] 2024-11-30: Call Masking доп.: endpoint `/api/mobile/calls/end` фиксирует окончание сессии и длительность.
- [x] 2024-11-30: Сортировка по доверию: параметр `sort=trust` для выдачи объявлений (seller_score desc, price_anomaly asc).
- [x] 2024-11-30: Команда `listings:recalculate-pricing` (cron 03:00) пересчитывает IMV/бейджи/аномалии и seller_score для активных объявлений.
- [x] 2024-11-30: Mobile API/клиент: отдаём price_badge/price_anomaly/imv/otd/seller_score, сортировку trust; отображение бейджа/OTD/анomaly/trust в мобильных карточках.
- [x] 2024-11-30: Safe Deal API расширен: эндпоинты escrow index/capture/release, ресурс `EscrowTransactionResource`.
- [x] 2024-11-30: Добавлена конфигурация `escrow` в `config/services.php` (provider/webhook/sandbox), README обновлён секциями Safe Deal/Call Masking/pSEO.
- [x] 2024-11-30: Каркас провайдера эскроу: интерфейс + `StubEscrowProvider`, `EscrowService` выбирает провайдер из конфига.
- [x] 2024-11-30: Добавлен `IdramEscrowProvider` (заглушка), параметры `IDRAM_ESCROW_URL/IDRAM_ESCROW_KEY` в конфиге.
- [x] 2024-11-30: Добавлен `TelcellEscrowProvider` (заглушка), параметры `TELCELL_ESCROW_URL/TELCELL_ESCROW_KEY` в конфиге; README обновлён по провайдерам.
- [x] 2024-11-30: Escrow webhook защита: middleware `escrow.webhook` (X-Escrow-Signature), эндпоинт webhook вынесен без Sanctum.
