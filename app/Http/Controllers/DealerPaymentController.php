<?php

namespace App\Http\Controllers;

use App\Models\DealerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class DealerPaymentController extends Controller
{
    public function showCheckout()
    {
        // Здесь будет Stripe Checkout; пока выводим заглушку
        return view('dealers.checkout');
    }

    public function createCheckout(Request $request)
    {
        $user = $request->user();
        $priceId = config('services.stripe.dealer_price_id');
        $successUrl = route('dealer.payment.success');
        $cancelUrl = url()->previous() ?: route('dealer.payment');

        if (!$priceId || !config('services.stripe.secret')) {
            return back()->withErrors(['payment' => __('dealer.pay_fail')]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::create([
                'mode' => 'subscription',
                'customer_email' => $user->email,
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);
        } catch (ApiErrorException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $user = Auth::user();
        if ($user && !$user->is_dealer) {
            $user->is_dealer = true;
            $user->save();

            DealerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $user->name ?? __('dealer.become'),
                    'slug' => Str::slug(($user->name ?: 'dealer') . '-' . Str::random(6)),
                ]
            );
        }

        return redirect()->route('listings.create-choice')->with('success', __('dealer.become'));
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            if ($endpointSecret) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            } else {
                $event = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\Throwable $e) {
            return response()->json(['status' => 'invalid'], 400);
        }

        if (($event->type ?? '') === 'checkout.session.completed') {
            $session = $event->data->object;
            $userId = $session->metadata->user_id ?? null;
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user && !$user->is_dealer) {
                    $user->is_dealer = true;
                    $user->save();

                    DealerProfile::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'company_name' => $user->name ?? __('dealer.become'),
                            'slug' => Str::slug(($user->name ?: 'dealer') . '-' . Str::random(6)),
                        ]
                    );
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
