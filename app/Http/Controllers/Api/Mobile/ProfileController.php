<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\NotificationSettingRequest;
use App\Http\Requests\Api\Mobile\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PhoneVerificationService $phoneVerification
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (array_key_exists('timezone', $data)) {
            $user->timezone = $data['timezone'];
        }

        if (array_key_exists('language', $data) && $data['language']) {
            $user->language = $data['language'];
        }

        if (array_key_exists('phone', $data)) {
            $phone = $data['phone'] ? $this->phoneVerification->normalize($data['phone']) : null;
            $hasChanged = $phone !== $user->phone;

            if ($hasChanged) {
                if ($phone) {
                    if (empty($data['verification_code']) || ! $this->phoneVerification->verify($data['phone'], $data['verification_code'])) {
                        return $this->error(__('Неверный или просроченный код подтверждения.'), 422);
                    }

                    $user->phone = $phone;
                    $user->phone_verified_at = now();
                } else {
                    $user->phone = null;
                    $user->phone_verified_at = null;
                }
            }
        }

        $user->save();

        return $this->success(new UserResource($user->fresh()), __('Профиль обновлён.'));
    }

    public function notificationSettings(Request $request): JsonResponse
    {
        return $this->success($request->user()->notification_settings);
    }

    public function updateNotificationSettings(NotificationSettingRequest $request): JsonResponse
    {
        $user = $request->user();
        $updates = array_filter($request->validated(), fn ($value) => $value !== null);

        $user->notification_settings = array_merge($user->notification_settings ?? [], $updates);
        $user->save();

        return $this->success($user->notification_settings, __('Настройки уведомлений обновлены.'));
    }
}
