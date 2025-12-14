# 🎯 План SEO оптимизации для idrom.am (Армения)

## Целевые запросы на русском и армянском:
- **Русский**: аукцион, машина, авто, покупка авто, продажа авто, объявления авто, авто из США, copart, idrom
- **Армянский**: մեքենա (машина), ավտո (авто), աճուրդ (аукцион), վաճառք (продажа), գնել մեքենա (купить машину), ամերիկացի մեքենա (американские машины)

## 1️⃣ Meta-теги (ГОТОВО ✅)
Уже есть в `resources/views/layouts/app.blade.php`:
- Title, Description, Keywords
- Open Graph
- Google Analytics
- Google Tag Manager

## 2️⃣ Добавить армянские ключевые слова

### В `config/app.php` добавить:
```php
'seo' => [
    'keywords' => [
        'ru' => 'idrom, авто, аукцион, машины, покупка авто, продажа авто, copart, америка, объявления, Армения',
        'hy' => 'idrom, մեքենա, աճուրդ, ավտո, գնել մեքենա, վաճառք, ամերիկա, copart, Հայաստան',
        'en' => 'idrom, car, auction, auto, buy car, sell car, copart, america, listings, Armenia',
    ],
    'title' => [
        'ru' => 'idrom.am — Авто аукционы и объявления в Армении',
        'hy' => 'idrom.am — Ավտո աճուրդներ և հայտարարություններ Հայաստանում',
        'en' => 'idrom.am — Car Auctions and Listings in Armenia',
    ],
    'description' => [
        'ru' => 'Покупка и продажа автомобилей в Армении. Аукционы Copart, локальные объявления, машины из США. Подбор авто под ключ.',
        'hy' => 'Մեքենաների գնում և վաճառք Հայաստանում։ Copart աճուրդներ, տեղական հայտարարություններ, ամերիկյան մեքենաներ։',
        'en' => 'Buy and sell cars in Armenia. Copart auctions, local listings, cars from USA. Turnkey car selection.',
    ],
],
```

## 3️⃣ Sitemap.xml (ВАЖНО!)

Создать `public/sitemap.xml`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://idrom.am/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://idrom.am/listings</loc>
        <changefreq>hourly</changefreq>
        <priority>0.9</priority>
    </url>
    <!-- Динамические объявления генерировать через роут -->
</urlset>
```

## 4️⃣ Robots.txt (ВАЖНО!)

Создать `public/robots.txt`:
```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /api

Sitemap: https://idrom.am/sitemap.xml
```

## 5️⃣ Структурированные данные (Schema.org)

Добавить в каждую страницу объявления:
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Car",
  "name": "{{ $listing->title }}",
  "brand": "{{ $listing->make }}",
  "model": "{{ $listing->model }}",
  "vehicleModelDate": "{{ $listing->year }}",
  "mileageFromOdometer": {
    "@type": "QuantitativeValue",
    "value": "{{ $listing->mileage }}",
    "unitCode": "KMT"
  },
  "offers": {
    "@type": "Offer",
    "price": "{{ $listing->price }}",
    "priceCurrency": "AMD"
  },
  "image": "{{ $listing->main_photo }}"
}
</script>
```

## 6️⃣ Оптимизация контента

### Добавить на главную:
- H1: "Аукцион авто в Армении | idrom.am"
- H2: "Купить машину из США через Copart"
- H2: "Местные объявления о продаже авто"
- Текстовый блок 300-500 слов с ключевыми словами

### На армянском:
- H1: "Ավտո աճուրդ Հայաստանում | idrom.am"
- H2: "Գնել մեքենա ԱՄՆ-ից Copart-ի միջոցով"

## 7️⃣ Технические улучшения

### Скорость сайта:
```bash
# Включить кеширование
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Сжатие изображений
npm install imagemin imagemin-webp
```

### HTTPS:
- ✅ Убедиться что работает SSL
- Редирект с http на https

## 8️⃣ Локальное SEO (Армения)

### Google My Business:
1. Создать профиль компании
2. Указать адрес в Ереване
3. Добавить фото офиса
4. Получить отзывы

### Яндекс.Вебмастер:
1. Добавить сайт
2. Подтвердить права
3. Загрузить sitemap.xml

### Google Search Console:
1. Добавить сайт
2. Загрузить sitemap.xml
3. Проверить индексацию

## 9️⃣ Внешние ссылки (Backlinks)

### Где публиковать:
- List.am (форум/блог)
- Facebook группы (авто Армения)
- Telegram каналы
- Армянские автофорумы
- YouTube (обзоры)

## 🔟 Контент-маркетинг

### Блог с полезными статьями:
1. "Как купить машину с аукциона Copart"
2. "Топ-10 авто для Армении 2025"
3. "Растаможка авто в Армении: пошаговая инструкция"
4. "Как проверить авто перед покупкой"

На русском и армянском!

## 📊 Отслеживание

### Настроить:
- Google Analytics (✅ уже есть)
- Yandex Metrika
- Отслеживание позиций по ключевым словам

### Инструменты:
- Google Search Console
- Ahrefs / Semrush (платно)
- Serpstat (для СНГ)

## 🚀 Быстрые победы (сделать сейчас):

1. Добавить hreflang для мультиязычности
2. Улучшить meta description на всех страницах
3. Добавить alt-текст ко всем изображениям
4. Создать sitemap.xml
5. Оптимизировать robots.txt
6. Добавить Schema.org markup

## 📈 KPI для отслеживания:

- Позиции по ключевым запросам
- Органический трафик
- Количество конверсий (заявки)
- Время на сайте
- Показатель отказов

---

**Приоритет**: Начать с sitemap.xml, robots.txt и Schema.org — это даст быстрый эффект!
