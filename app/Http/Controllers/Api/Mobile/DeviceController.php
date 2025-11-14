<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\DeviceRegisterRequest;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $devices = $request->user()->devices()->latest('updated_at')->get();

        return $this->success(DeviceResource::collection($devices));
    }

    public function store(DeviceRegisterRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $device = Device::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_id' => $data['device_id'],
            ],
            [
                'platform' => $data['platform'],
                'fcm_token' => $data['fcm_token'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'last_used_at' => now(),
            ]
        );

        return $this->success(new DeviceResource($device), __('Устройство зарегистрировано.'));
    }

    public function destroy(Request $request, string $device)
    {
        $request->user()
            ->devices()
            ->where(function ($query) use ($device) {
                $query->where('device_id', $device)
                    ->orWhere('id', $device);
            })
            ->delete();

        return $this->success(message: __('Устройство удалено.'));
    }
}
