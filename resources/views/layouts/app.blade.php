<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- App icons -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="alternate icon" href="{{ asset('images/logo.png') }}">

    <!-- Styles -->
    @stack('styles')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased" style="background-color: var(--brand-light-gray);">
<div class="min-h-screen">
    @include('layouts.navigation')

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
