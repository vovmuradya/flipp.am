<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialAuthController extends Controller
{
    /**
     * Ограничиваем список поддерживаемых провайдеров.
     *
     * @var array<string>
     */
    protected array $supportedProviders = [
        'google',
        'facebook',
    ];

    /**
     * Перенаправление пользователя на OAuth-провайдера.
     */
    public function redirect(string $provider): SymfonyRedirectResponse|RedirectResponse
    {
        $this->ensureSupportedProvider($provider);

        $callbackUrl = $this->resolveCallbackUrl($provider);

        return Socialite::driver($provider)
            ->redirectUrl($callbackUrl)
            ->stateless()
            ->redirect();
    }

    /**
     * Обработка callback-а от провайдера.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->ensureSupportedProvider($provider);

        $callbackUrl = $this->resolveCallbackUrl($provider);

        try {
            $socialUser = Socialite::driver($provider)
                ->redirectUrl($callbackUrl)
                ->stateless()
                ->user();
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors([
                    'provider' => 'Не удалось авторизоваться через ' . ucfirst($provider) . '. Попробуйте ещё раз.',
                ]);
        }

        $email = $socialUser->getEmail();

        if (!$email) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Провайдер ' . ucfirst($provider) . ' не вернул email. Добавьте email в аккаунте или используйте регистрацию через сайт.',
                ]);
        }

        $user = User::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (!$user) {
            $user = User::query()->firstOrNew(['email' => $email]);

            if (!$user->exists) {
                $user->name = $socialUser->getName() ?: ($socialUser->getNickname() ?: Str::before($email, '@'));
                $user->password = Str::random(32);
                $user->role = $user->role ?? 'individual';
            }

            $user->provider = $provider;
            $user->provider_id = $socialUser->getId();
        }

        $user->provider_token = $socialUser->token ?? null;
        $user->provider_refresh_token = $socialUser->refreshToken ?? null;

        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        if (!$user->avatar && $socialUser->getAvatar()) {
            $user->avatar = $socialUser->getAvatar();
        }

        $user->save();

        Auth::login($user, true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    protected function ensureSupportedProvider(string $provider): void
    {
        if (!in_array($provider, $this->supportedProviders, true)) {
            abort(404);
        }
    }

    protected function resolveCallbackUrl(string $provider): string
    {
        $configured = config("services.{$provider}.redirect");
        $currentRequest = request();

        if ($currentRequest) {
            $host = $currentRequest->getHost();
            $scheme = $currentRequest->getScheme();
            $port = $currentRequest->getPort();

            $needsDynamic = false;

            if ($configured) {
                $configuredHost = parse_url($configured, PHP_URL_HOST);
                $configuredPortValue = parse_url($configured, PHP_URL_PORT);

                if (!$configuredHost || strcasecmp($configuredHost, $host ?? '') !== 0) {
                    $needsDynamic = true;
                } elseif ($port !== null) {
                    $currentPort = (int) $port;
                    $expectedPort = $configuredPortValue !== null ? (int) $configuredPortValue : null;

                    if ($expectedPort === null && !in_array($currentPort, [80, 443], true)) {
                        $needsDynamic = true;
                    } elseif ($expectedPort !== null && $expectedPort !== $currentPort) {
                        $needsDynamic = true;
                    }
                }
            } else {
                $needsDynamic = true;
            }

            if ($needsDynamic) {
                $authority = $host ?? 'localhost';
                if ($port && !in_array((int) $port, [80, 443], true)) {
                    $authority .= ':' . $port;
                }

                return "{$scheme}://{$authority}/auth/{$provider}/callback";
            }
        }

        if ($configured) {
            return $configured;
        }

        return rtrim(config('app.url'), '/') . "/auth/{$provider}/callback";
    }
}
