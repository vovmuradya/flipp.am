<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneVerificationController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationService $service
    ) {
    }

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        $this->service->sendCode($data['phone']);

        return response()->json([
            'success' => true,
            'message' => __('Код отправлен. Пожалуйста, проверьте SMS.'),
        ]);
    }
}
