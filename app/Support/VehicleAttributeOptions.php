<?php

namespace App\Support;

class VehicleAttributeOptions
{
    /**
     * @return array<string,string>
     */
    public static function colors(): array
    {
        return [
            'white' => 'Белый',
            'black' => 'Черный',
            'gray' => 'Серый',
            'silver' => 'Серебристый',
            'blue' => 'Синий',
            'red' => 'Красный',
            'green' => 'Зеленый',
            'brown' => 'Коричневый',
            'beige' => 'Бежевый',
            'yellow' => 'Желтый',
            'orange' => 'Оранжевый',
            'purple' => 'Фиолетовый',
            'gold' => 'Золотой',
            'burgundy' => 'Бордовый',
            'other' => 'Другой',
        ];
    }

    /**
     * @return array<string,string>
     */
    public static function bodyTypes(): array
    {
        return [
            'sedan' => 'Седан',
            'suv' => 'SUV / Внедорожник',
            'coupe' => 'Купе',
            'hatchback' => 'Хэтчбек',
            'wagon' => 'Универсал',
            'pickup' => 'Пикап',
            'minivan' => 'Минивэн',
            'convertible' => 'Кабриолет',
        ];
    }

    /**
     * @return array<string,string>
     */
    public static function transmissions(): array
    {
        return [
            'automatic' => 'Автомат',
            'manual' => 'Механика',
            'cvt' => 'CVT',
            'semi-automatic' => 'Робот',
        ];
    }

    /**
     * @return array<string,string>
     */
    public static function fuelTypes(): array
    {
        return [
            'gasoline' => 'Бензин',
            'diesel' => 'Дизель',
            'hybrid' => 'Гибрид',
            'electric' => 'Электро',
            'lpg' => 'Газ',
        ];
    }

    public static function colorLabel(?string $key): ?string
    {
        if (!$key) {
            return null;
        }

        return self::colors()[$key] ?? null;
    }
}
