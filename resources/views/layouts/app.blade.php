<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MPXKHNR8');</script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $defaultTitle = __('idrom.am — Авто объявления и поиск автомобилей в Армении');
        $metaTitle = trim($__env->yieldContent('meta_title')) ?: $defaultTitle;
        $defaultDescription = __('idrom.am — платформа объявлений для продажи и покупки автомобилей, подбор лотов с аукционов и локальных дилеров.');
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: $defaultDescription;
        $metaKeywords = trim($__env->yieldContent('meta_keywords')) ?: 'idrom, авто, объявления, покупка авто, аукционы, Армения';
        $metaRobots = trim($__env->yieldContent('meta_robots')) ?: 'index,follow';
        $metaCanonical = trim($__env->yieldContent('meta_canonical')) ?: url()->current();
        $metaOgImage = trim($__env->yieldContent('meta_image')) ?: asset('logo-512.png');
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="{{ $metaRobots }}">
    <link rel="canonical" href="{{ $metaCanonical }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512x512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5VBKSM39JN"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-5VBKSM39JN');
    </script>

    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9290639823583418" crossorigin="anonymous"></script>

    <!-- SEO Meta -->
    <meta property="og:type" content="@yield('meta_og_type', 'website')">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $metaCanonical }}">
    <meta property="og:site_name" content="{{ config('app.name', 'idrom.am') }}">
    <meta property="og:image" content="{{ $metaOgImage }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaOgImage }}">
    <meta name="twitter:site" content="@idromam">
    @stack('meta')
    @php
        $organizationLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'idrom.am',
            'url' => config('app.url', url('/')),
            'logo' => $metaOgImage,
            'sameAs' => [
                'https://www.facebook.com/idromam',
                'https://t.me/idromam',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'email' => 'info@idrom.am',
                'contactType' => 'Customer Support',
                'areaServed' => 'AM'
            ],
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode($organizationLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <!-- Styles -->
    @stack('styles')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased" style="background-color: var(--brand-light-gray);">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MPXKHNR8"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="min-h-screen">
    @include('layouts.navigation')

    <div class="bg-amber-50 border-b border-amber-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 text-sm text-amber-900 flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-amber-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1 3a1 1 0 00-.993.883L9 10v4a1 1 0 001.993.117L11 14v-4a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <p class="leading-snug">
                {!! __(
                    'site_demo_notice',
                    [
                        'email' => sprintf(
                            '<a href="mailto:%1$s" class="font-semibold underline text-amber-900 hover:text-amber-700">%1$s</a>',
                            __('site_demo_notice_email')
                        ),
                    ]
                ) !!}
            </p>
        </div>
    </div>

    <!-- Page Heading -->
    @isset($header)
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- Page Content -->
    <main>
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>

    @include('layouts.footer')
</div>

@include('layouts.partials.locale-modal')
@include('components.auction.countdown-script')

@stack('scripts')

<!-- Alpine.js fallback -->
<script>
    (function () {
        function loadAlpineFallback() {
            if (window.Alpine) return;

            const script = document.createElement('script');
            script.defer = true;
            script.src = "{{ asset('vendor/alpinejs/alpine-3.15.0.min.js') }}";
            script.addEventListener('load', function () {
                if (window.Alpine && typeof window.Alpine.start === 'function') {
                    window.Alpine.start();
                }
            });
            document.head.appendChild(script);
        }

        function scheduleFallback() {
            window.setTimeout(loadAlpineFallback, 200);
        }

        if (window.Alpine) {
            return;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', scheduleFallback);
        } else {
            scheduleFallback();
        }
    })();
</script>

</body>
</html>
