<?php

namespace App\Support;

/**
 * Persian and Arabic-Indic digits arrive from phone keyboards and from text
 * pasted out of Persian documents. Every numeric input normalises through
 * here before validation or storage.
 */
class Digits
{
    private const MAP = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    public static function toEnglish(?string $value): ?string
    {
        return $value === null ? null : strtr($value, self::MAP);
    }

    /**
     * English digits only — separators, spaces and dashes removed.
     */
    public static function onlyNumbers(?string $value): string
    {
        return preg_replace('/\D/', '', (string) self::toEnglish($value)) ?? '';
    }
}
