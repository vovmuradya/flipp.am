<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Административная панель - Idrom.am</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --admin-sidebar-width: 250px;
            --admin-header-height: 60px;
        }
        
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8fafc;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: var(--admin-sidebar-width);
            background: #1e293b;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        
        .admin-content {
            flex: 1;
            margin-left: var(--admin-sidebar-width);
            padding: 20px;
        }
        
        .admin-header {
            height: var(--admin-header-height);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        
        .nav-link {
            display: block;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: #334155;
            color: white;
        }
        
        .admin-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 24px;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .stat-label {
            color: #64748b;
            margin-top: 5px;
        }
    </style>
</head>
<body class="antialiased">
    <div class="admin-layout">
        <!-- Боковое меню -->
        <div class="admin-sidebar">
            <div class="p-4">
                <h2 class="text-xl font-bold">Idrom.am Admin</h2>
            </div>
            
            <nav>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Дашборд
                </a>

                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Пользователи
                </a>

                <a href="{{ route('admin.listings.index') }}" class="nav-link {{ request()->routeIs('admin.listings.*') ? 'active' : '' }}">
                    <i class="fas fa-list"></i> Объявления
                </a>

                <a href="{{ route('admin.moderation.index') }}" class="nav-link {{ request()->routeIs('admin.moderation') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i> Модерация
                </a>

                <a href="{{ route('admin.support.index') }}" class="nav-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
                    <i class="fas fa-headset"></i> Поддержка
                </a>

                <a href="{{ route('admin.errors.index') }}" class="nav-link {{ request()->routeIs('admin.errors.*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle"></i> Ошибки
                </a>

                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> Категории
                </a>

                <a href="{{ route('admin.analytics') }}" class="nav-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Аналитика
                </a>
                <a href="{{ route('admin.analytics.detailed') }}" class="nav-link {{ request()->routeIs('admin.analytics.detailed') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Детальная аналитика
                </a>

                <a href="{{ route('admin.idram-imid.index') }}" class="nav-link {{ request()->routeIs('admin.idram-imid.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Idram & imID
                </a>

                <a href="{{ route('admin.support-tickets.index') }}" class="nav-link {{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i> Тикеты поддержки
                </a>

                <a href="{{ route('admin.registration-errors.index') }}" class="nav-link {{ request()->routeIs('admin.registration-errors.*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-circle"></i> Ошибки регистрации
                </a>

                <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Настройки
                </a>
            </nav>
        </div>

        <!-- Основной контент -->
        <div class="admin-content">
            <!-- Заголовок страницы -->
            <div class="admin-header">
                <div class="flex items-center justify-between w-full">
                    <h1 class="text-xl font-semibold">@yield('title', 'Административная панель')</h1>
                    <div class="flex items-center space-x-4">
                        <span>Привет, {{ Auth::user()->name }}!</span>
                        <a href="{{ route('home') }}" class="text-blue-600 hover:underline">На сайт</a>
                        <a href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                           class="text-red-600 hover:underline">
                            Выйти
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>

            <!-- Основной контент страницы -->
            <main class="mt-6">
                @yield('content')
            </main>
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>