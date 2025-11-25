<?php

namespace App\Support;

class VehicleAttributeOptions
{
    /**
     * @return array<string,string>
     */
    public static function colors(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'hy') {
            return [
                'white' => 'Սպիտակ',
                'black' => 'Սև',
                'gray' => 'Մոխրագույն',
                'silver' => 'Արծաթագույն',
                'blue' => 'Կապույտ',
                'red' => 'Կարմիր',
                'green' => 'Կանաչ',
                'brown' => 'Շագանակագույն',
                'beige' => 'Բեժ',
                'yellow' => 'Դեղին',
                'orange' => 'Նարնջագույն',
                'purple' => 'Մանուշակագույն',
                'gold' => 'Ոսկեգույն',
                'burgundy' => 'Բորդո',
                'other' => 'Այլ',
            ];
        }

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
        $locale = app()->getLocale();

        if ($locale === 'hy') {
            return [
                'sedan' => 'Սեդան',
                'suv' => 'Ամենագնաց / SUV',
                'coupe' => 'Կուպե',
                'hatchback' => 'Հեթչբեք',
                'wagon' => 'Ունիվերսալ',
                'pickup' => 'Պիկապ',
                'minivan' => 'Մինիվեն',
                'convertible' => 'Կաբրիոլետ',
            ];
        }

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
        $locale = app()->getLocale();

        if ($locale === 'hy') {
            return [
                'automatic' => 'Ավտոմատ',
                'manual' => 'Մեխանիկա',
                'cvt' => 'Վարիատոր',
                'semi-automatic' => 'Ռոբոտ',
            ];
        }

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
        $locale = app()->getLocale();

        if ($locale === 'hy') {
            return [
                'gasoline' => 'Բենզին',
                'diesel' => 'Դիզել',
                'hybrid' => 'Հիբրիդ',
                'electric' => 'Էլեկտրո',
                'lpg' => 'Գազ',
            ];
        }

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
