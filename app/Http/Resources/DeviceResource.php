<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Device */
class DeviceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device_id,
            'platform' => $this->platform,
            'fcm_token' => $this->fcm_token,
            'app_version' => $this->app_version,
            'last_used_at' => optional($this->last_used_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
