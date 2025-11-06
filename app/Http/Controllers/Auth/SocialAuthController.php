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

        $this->applyDynamicRedirect($provider);

        return Socialite::driver($provider)
            ->stateless()
            ->redirect();
    }

    /**
     * Обработка callback-а от провайдера.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->ensureSupportedProvider($provider);

        $this->applyDynamicRedirect($provider);

        try {
            $socialUser = Socialite::driver($provider)
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

    protected function applyDynamicRedirect(string $provider): void
    {
        $routeName = 'auth.provider.callback';

        $configured = config("services.{$provider}.redirect");
        $fallback = rtrim(config('app.url'), '/') . "/auth/{$provider}/callback";

        if ($configured && $configured !== $fallback) {
            // Используем явно заданный redirect URI (например, прод-домен).
            return;
        }

        $callbackUrl = $fallback;

        if (!app()->runningInConsole()) {
            $currentRequest = request();

            if ($currentRequest) {
                $host = $currentRequest->getSchemeAndHttpHost();
                $callbackUrl = rtrim($host, '/') . "/auth/{$provider}/callback";
            }
        }

        if ($callbackUrl) {
            config(["services.{$provider}.redirect" => $callbackUrl]);
        }
    }
}
