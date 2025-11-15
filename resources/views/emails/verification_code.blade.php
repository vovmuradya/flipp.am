<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Код подтверждения email') }}</title>
</head>
<body>
<p>{{ __('Здравствуйте!') }}</p>
<p>
    {{ __('Ваш код подтверждения:') }}
    <strong>{{ $code }}</strong>
</p>
<p>{{ __('Код действителен в течение 10 минут.') }}</p>
<p>{{ config('app.name') }}</p>
</body>
</html>
