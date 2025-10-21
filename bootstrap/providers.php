<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,

    // <-- ДОБАВЬТЕ ЭТУ СТРОКУ В КОНЕЦ СПИСКА
    Spatie\Image\ImageServiceProvider::class,
];
