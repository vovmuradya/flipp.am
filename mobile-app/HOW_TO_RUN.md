# 📱 Как посмотреть приложение

## Вариант 1: В браузере (САМЫЙ ПРОСТОЙ) 🌐

```bash
cd mobile-app
flutter run -d chrome
```

Откроется в Chrome и будет выглядеть как на телефоне (мобильный вид).

**Чтобы открыть DevTools в режиме телефона:**
1. В Chrome нажми F12
2. Нажми Ctrl+Shift+M (или иконку телефона вверху)
3. Выбери "iPhone 12 Pro" или "Pixel 5"

---

## Вариант 2: Android Эмулятор (КАК НА РЕАЛЬНОМ ТЕЛЕФОНЕ) 📱

### Шаг 1: Установи Android Studio
```bash
# Скачай Android Studio
wget https://redirector.gvt1.com/edgedl/android/studio/ide-zips/2024.2.1.11/android-studio-2024.2.1.11-linux.tar.gz

# Распакуй
tar -xzf android-studio-*.tar.gz
cd android-studio/bin
./studio.sh
```

### Шаг 2: Создай виртуальный телефон
1. Открой Android Studio
2. Tools → Device Manager
3. Create Device → Pixel 6 → Next
4. Выбери Android 13 (API 33) → Next → Finish

### Шаг 3: Запусти эмулятор
```bash
# Проверь доступные эмуляторы
flutter emulators

# Запусти (например Pixel_6)
flutter emulators --launch Pixel_6

# Подожди пока эмулятор загрузится (~30 сек)

# Запусти приложение
cd mobile-app
flutter run
```

---

## Вариант 3: На реальном телефоне Android 📲

### Подготовка телефона:
1. **Settings → About Phone** → Тапай 7 раз на "Build number"
2. **Settings → Developer Options** → Включи "USB Debugging"
3. Подключи телефон USB к компьютеру
4. На телефоне подтверди "Allow USB debugging"

### Запуск:
```bash
# Проверь что телефон виден
flutter devices

# Запусти приложение
cd mobile-app
flutter run
```

Приложение установится на телефон автоматически!

---

## Вариант 4: iOS Симулятор (только на Mac) 🍎

```bash
# Открой симулятор
open -a Simulator

# Запусти
cd mobile-app
flutter run
```

---

## 🔥 БЫСТРЫЙ СТАРТ (рекомендую):

```bash
cd mobile-app
flutter run -d chrome
```

Откроется в браузере, нажми **F12 → Ctrl+Shift+M** для мобильного вида!

---

## 🎨 Как выглядит приложение:

### Главный экран
- Список объявлений с фото
- Карусель избранных
- Фильтры и сортировка

### Поиск (вкладка 🔍)
- Поле поиска
- Кнопка фильтров (справа)
- Результаты поиска

### Профиль (вкладка 👤)
- Форма входа / профиль пользователя
- Кнопки: My Listings, My Auctions, Calculator, Notifications

### Кнопка + (центр внизу)
- 4 опции создания объявления
- Vehicle / Parts / Copart Import / Quick Sell

---

## ⚡ Hot Reload

Когда приложение запущено, можно редактировать код и сразу видеть изменения:
- Нажми **R** в консоли = hot reload
- Нажми **Shift+R** = hot restart

---

## 🐛 Если не запускается:

```bash
# Очисти кэш
flutter clean
flutter pub get

# Попробуй снова
flutter run -d chrome
```

---

## 📱 Эмулятор vs Реальный телефон:

| Функция | Эмулятор | Реальный телефон |
|---------|----------|------------------|
| Скорость | Медленнее | Быстрее ✅ |
| Камера (фото) | Не работает | Работает ✅ |
| GPS | Эмуляция | Реальный ✅ |
| Push уведомления | Не работает | Работает ✅ |
| Удобство | Удобно для разработки ✅ | Для финального теста ✅ |

**Для быстрого просмотра → Chrome**  
**Для полноценного теста → Реальный телефон**
