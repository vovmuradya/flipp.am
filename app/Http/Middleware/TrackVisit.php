<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Visit;
use Symfony\Component\HttpFoundation\Response;
use Jenssegers\Agent\Facades\Agent;

class TrackVisit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Не отслеживаем посещения администраторов в административной панели
        if ($request->is('admin/*')) {
            return $response;
        }

        // Создаем запись о посещении
        Visit::create([
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'page_url' => $request->fullUrl(),
            'page_title' => $this->getPageTitle($request),
            'country' => $this->getCountry($request),
            'city' => $this->getCity($request),
            'device_type' => $this->getDeviceType(),
            'browser' => $this->getBrowser(),
            'platform' => $this->getPlatform(),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_term' => $request->query('utm_term'),
            'utm_content' => $request->query('utm_content'),
            'user_id' => Auth::id(),
        ]);

        return $response;
    }

    /**
     * Получает заголовок страницы (простая реализация)
     */
    private function getPageTitle(Request $request): ?string
    {
        // В реальном приложении можно использовать более сложную логику для получения заголовка
        return $request->path();
    }

    /**
     * Получает страну пользователя (простая реализация)
     */
    private function getCountry(Request $request): ?string
    {
        // В реальном приложении можно использовать сервис геолокации
        return null;
    }

    /**
     * Получает город пользователя (простая реализация)
     */
    private function getCity(Request $request): ?string
    {
        // В реальном приложении можно использовать сервис геолокации
        return null;
    }

    /**
     * Определяет тип устройства
     */
    private function getDeviceType(): ?string
    {
        if (Agent::isMobile()) {
            return 'mobile';
        } elseif (Agent::isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Определяет браузер
     */
    private function getBrowser(): ?string
    {
        if (Agent::is('Chrome')) {
            return 'Chrome';
        } elseif (Agent::is('Firefox')) {
            return 'Firefox';
        } elseif (Agent::is('Safari')) {
            return 'Safari';
        } elseif (Agent::is('Edge')) {
            return 'Edge';
        } elseif (Agent::is('IE')) {
            return 'Internet Explorer';
        } else {
            return 'Other';
        }
    }

    /**
     * Определяет платформу
     */
    private function getPlatform(): ?string
    {
        if (Agent::is('Windows')) {
            return 'Windows';
        } elseif (Agent::is('Mac')) {
            return 'MacOS';
        } elseif (Agent::is('Linux')) {
            return 'Linux';
        } elseif (Agent::is('Android')) {
            return 'Android';
        } elseif (Agent::is('iOS')) {
            return 'iOS';
        } else {
            return 'Other';
        }
    }
}
