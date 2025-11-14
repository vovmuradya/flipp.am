<?php

namespace App\Support;

class SearchQueryHelper
{
    public static function expand(string $input): string
    {
        $trimmed = trim($input);
        if ($trimmed === '') {
            return '*';
        }

        if ($trimmed === '*') {
            return '*';
        }

        $variants = self::variants($trimmed);

        return empty($variants) ? $trimmed : implode(' ', $variants);
    }

    /**
     * @return array<int,string>
     */
    public static function variants(string $input): array
    {
        $term = trim($input);
        if ($term === '' || $term === '*') {
            return $term === '' ? [] : ['*'];
        }

        $variants = [];
        $normalized = preg_replace('/\s+/u', ' ', $term);
        $normalized = is_string($normalized) ? trim($normalized) : trim($term);

        if ($normalized !== '') {
            $variants[] = $normalized;
            $variants[] = self::collapseRepeats($normalized);

            $transliterated = self::transliterate($normalized);
            if ($transliterated !== $normalized) {
                $variants[] = $transliterated;
                $variants[] = self::collapseRepeats($transliterated);
            }
        }

        $variants = array_values(array_filter(array_unique($variants), function ($value) {
            return is_string($value) && trim($value) !== '' && $value !== '*';
        }));

        return $variants;
    }

    private static function collapseRepeats(string $value): string
    {
        return preg_replace('/(.)\1+/u', '$1', $value);
    }

    private static function transliterate(string $value): string
    {
        static $map = [
            // Armenian
            'ա' => 'a', 'բ' => 'b', 'գ' => 'g', 'դ' => 'd', 'ե' => 'e', 'զ' => 'z', 'է' => 'e', 'ը' => 'y',
            'թ' => 't', 'ժ' => 'zh', 'ի' => 'i', 'լ' => 'l', 'խ' => 'kh', 'ծ' => 'ts', 'կ' => 'k', 'հ' => 'h',
            'ձ' => 'dz', 'ղ' => 'gh', 'ճ' => 'ch', 'մ' => 'm', 'յ' => 'y', 'ն' => 'n', 'շ' => 'sh', 'ո' => 'o',
            'չ' => 'ch', 'պ' => 'p', 'ջ' => 'j', 'ռ' => 'r', 'ս' => 's', 'վ' => 'v', 'տ' => 't', 'ր' => 'r',
            'ց' => 'ts', 'փ' => 'p', 'ք' => 'q', 'և' => 'ev', 'օ' => 'o', 'ֆ' => 'f',
            'Ա' => 'A', 'Բ' => 'B', 'Գ' => 'G', 'Դ' => 'D', 'Ե' => 'E', 'Զ' => 'Z', 'Է' => 'E', 'Ը' => 'Y',
            'Թ' => 'T', 'Ժ' => 'Zh', 'Ի' => 'I', 'Լ' => 'L', 'Խ' => 'Kh', 'Ծ' => 'Ts', 'Կ' => 'K', 'Հ' => 'H',
            'Ձ' => 'Dz', 'Ղ' => 'Gh', 'Ճ' => 'Ch', 'Մ' => 'M', 'Յ' => 'Y', 'Ն' => 'N', 'Շ' => 'Sh', 'Ո' => 'O',
            'Չ' => 'Ch', 'Պ' => 'P', 'Ջ' => 'J', 'Ռ' => 'R', 'Ս' => 'S', 'Վ' => 'V', 'Տ' => 'T', 'Ր' => 'R',
            'Ց' => 'Ts', 'Փ' => 'P', 'Ք' => 'Q', 'Օ' => 'O', 'Ֆ' => 'F',
            // Cyrillic (Russian)
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
        ];

        static $digraphs = [
            'ու' => 'u',
            'ՈՒ' => 'U',
        ];

        $result = '';
        $length = mb_strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($value, $i, 1);
            $pair = null;

            if ($i + 1 < $length) {
                $pair = mb_substr($value, $i, 2);
            }

            if ($pair && isset($digraphs[$pair])) {
                $result .= $digraphs[$pair];
                $i++;
                continue;
            }

            $result .= $map[$char] ?? $char;
        }

        return $result;
    }

    public static function normalizeToken(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed === '*') {
            return '';
        }

        $lower = mb_strtolower($trimmed);
        $collapsed = self::collapseRepeats($lower);
        $transliterated = self::transliterate($collapsed);
        $normalized = mb_strtolower(trim($transliterated));
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return $normalized;
    }
}
