<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEscrowWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.escrow.webhook_secret');
        $token = $request->header('X-Escrow-Signature');

        if ($secret && (!is_string($token) || !hash_equals($secret, $token))) {
            abort(403, 'Invalid webhook signature');
        }

        return $next($request);
    }
}
