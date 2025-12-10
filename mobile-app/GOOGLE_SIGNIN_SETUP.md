# 🔐 Google Sign-In Setup

## ✅ Что уже есть:

1. ✅ Google OAuth настроен на сервере
2. ✅ Client ID: `1048690326353-u2bt7ahobnaovahefpfl2gel86an4j39.apps.googleusercontent.com`
3. ✅ Пакет `google_sign_in` добавлен в pubspec.yaml
4. ✅ API метод `loginWithGoogle()` готов в ApiClient
5. ✅ UI кнопки "Войти через Google" готов

## 📱 Что нужно сделать:

### 1. Настроить Android (5 минут)

#### Шаг 1: Получить SHA-1 fingerprint

```bash
cd android
./gradlew signingReport
```

Скопируй **SHA-1** из вывода (строка с `Variant: debug`, `SHA1:`)

#### Шаг 2: Добавить в Google Console

1. Открой: https://console.cloud.google.com/apis/credentials
2. Найди OAuth 2.0 Client ID
3. Добавь новый **Android** client:
   - Package name: `com.example.flipp_am`
   - SHA-1: (тот что скопировал выше)

#### Шаг 3: Обновить android/app/build.gradle

Добавь в `defaultConfig`:
```gradle
defaultConfig {
    ...
    resValue "string", "default_web_client_id", "1048690326353-u2bt7ahobnaovahefpfl2gel86an4j39.apps.googleusercontent.com"
}
```

---

### 2. Создать API endpoint на сервере (10 минут)

#### Файл: `routes/api.php`

Добавь:
```php
Route::post('/mobile/auth/google', [MobileAuthController::class, 'googleLogin']);
```

#### Файл: `app/Http/Controllers/Mobile/MobileAuthController.php`

Добавь метод:
```php
public function googleLogin(Request $request)
{
    $request->validate([
        'id_token' => 'required|string',
    ]);

    try {
        // Verify Google ID token
        $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
        $payload = $client->verifyIdToken($request->id_token);
        
        if (!$payload) {
            return response()->json(['message' => 'Invalid ID token'], 401);
        }

        $email = $payload['email'];
        $googleId = $payload['sub'];
        $name = $payload['name'] ?? '';

        // Find or create user
        $user = User::firstOrCreate(
            ['provider' => 'google', 'provider_id' => $googleId],
            [
                'email' => $email,
                'name' => $name,
                'email_verified_at' => now(),
            ]
        );

        // Update email if changed
        if ($user->email !== $email) {
            $user->update(['email' => $email]);
        }

        // Create token
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Google authentication failed'], 401);
    }
}
```

#### Установи Google API Client (если еще нет):

```bash
composer require google/apiclient:"^2.0"
```

---

### 3. Обновить UI в приложении

Замени в `lib/main.dart` кнопку Google на:

```dart
OutlinedButton.icon(
  style: OutlinedButton.styleFrom(
    padding: const EdgeInsets.symmetric(vertical: 14),
    side: BorderSide(color: Colors.white30),
    backgroundColor: Colors.white.withOpacity(0.05),
  ),
  onPressed: loading ? null : () async {
    try {
      final GoogleSignIn googleSignIn = GoogleSignIn(
        clientId: '1048690326353-u2bt7ahobnaovahefpfl2gel86an4j39.apps.googleusercontent.com',
      );
      
      final account = await googleSignIn.signIn();
      if (account == null) return;
      
      final auth = await account.authentication;
      final idToken = auth.idToken;
      
      if (idToken == null) {
        throw Exception('Failed to get ID token');
      }
      
      // Call API
      final profile = await widget.api.loginWithGoogle(idToken);
      
      // Success - update UI
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Logged in with Google!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Google Sign-In failed: $e')),
        );
      }
    }
  },
  icon: Icon(Icons.g_mobiledata, color: Colors.white, size: 28),
  label: const Text(
    'Войти через Google',
    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
  ),
)
```

---

## 🚀 Быстрый старт:

### Если хочешь запустить СЕЙЧАС:

```bash
# 1. Установи зависимости
cd mobile-app
flutter pub get

# 2. Настрой server endpoint
# (добавь метод googleLogin в MobileAuthController)

# 3. Запусти
flutter run
```

---

## ⚠️ Важно:

1. **SHA-1 fingerprint** нужен для каждой сборки:
   - Debug build (разработка)
   - Release build (production)

2. **Client ID** разный для:
   - Android
   - iOS
   - Web

3. **Redirect URI** не нужен для мобильных приложений (используется ID token)

---

## 📋 Checklist:

- [ ] Получить SHA-1 fingerprint
- [ ] Добавить Android client в Google Console
- [ ] Добавить `default_web_client_id` в build.gradle
- [ ] Создать `/api/mobile/auth/google` endpoint
- [ ] Установить `google/apiclient` в Laravel
- [ ] Обновить UI кнопки в Flutter
- [ ] Протестировать на эмуляторе
- [ ] Протестировать на реальном устройстве

---

## 🎉 После настройки:

1. Пользователь нажимает "Войти через Google"
2. Открывается Google аккаунты
3. Выбирает аккаунт
4. Приложение получает ID token
5. Отправляет на сервер
6. Сервер создает/находит пользователя
7. Возвращает JWT token
8. Пользователь залогинен!

**Время настройки: ~15-20 минут** ⏱️
