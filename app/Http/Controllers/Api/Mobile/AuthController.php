<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\LoginRequest;
use App\Http\Requests\Api\Mobile\RegisterRequest;
use App\Http\Requests\Api\Mobile\SendVerificationCodeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PhoneVerificationService $phoneVerification
    ) {
    }

    public function sendVerificationCode(SendVerificationCodeRequest $request): JsonResponse
    {
        $this->phoneVerification->sendCode($request->validated()['phone']);

        return $this->success(message: __('Код отправлен. Пожалуйста, проверьте SMS.'));
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $this->phoneVerification->verify($data['phone'], $data['verification_code'])) {
            return $this->error(__('Неверный или просроченный код подтверждения.'), 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_verified_at' => now(),
            'phone' => $this->phoneVerification->normalize($data['phone']),
            'phone_verified_at' => now(),
            'password' => $data['password'],
        ]);

        event(new Registered($user));

        $token = $user->createToken($this->deviceName($request))->plainTextToken;

        return $this->created(
            $this->tokenPayload($user, $token),
            __('Регистрация выполнена успешно.')
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $login = $data['login'];

        $user = User::query()
            ->where('email', $login)
            ->orWhere('phone', $this->phoneVerification->normalize($login))
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->error(__('Неверные учётные данные.'), 422);
        }

        $token = $user->createToken($this->deviceName($request))->plainTextToken;

        return $this->success(
            $this->tokenPayload($user, $token),
            __('Вы успешно вошли в систему.')
        );
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success(message: __('Выход выполнен.'));
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->unauthorized();
        }

        $request->user()->currentAccessToken()?->delete();

        $token = $user->createToken($this->deviceName($request))->plainTextToken;

        return $this->success(
            $this->tokenPayload($user, $token),
            __('Токен обновлён.')
        );
    }

    private function tokenPayload(User $user, string $token): array
    {
        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ];
    }

    private function deviceName(Request $request): string
    {
        return $request->input('device_name', 'mobile-app');
    }

    /**
     * Google OAuth authentication for mobile app
     */
    public function googleAuth(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return $this->error(__('Неверный токен Google'), 401);
            }

            // Создаём или обновляем пользователя
            $user = User::updateOrCreate(
                [
                    'provider' => 'google',
                    'provider_id' => $payload['sub'],
                ],
                [
                    'email' => $payload['email'],
                    'name' => $payload['name'] ?? '',
                    'email_verified_at' => now(),
                ]
            );

            // Создаём токен для мобильного приложения
            $token = $user->createToken($this->deviceName($request))->plainTextToken;

            return $this->success(
                $this->tokenPayload($user, $token),
                __('Успешная авторизация через Google')
            );

        } catch (\Exception $e) {
            return $this->error(__('Ошибка авторизации через Google: ') . $e->getMessage(), 500);
        }
    }
}
