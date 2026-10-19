<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;

/**
 * One place for Gregorian <-> Jalali conversion.
 *
 * Dates are always stored Gregorian in the database. Converting on the way
 * in and out keeps sorting, range filters and date arithmetic working; a
 * Jalali string in a date column breaks all three.
 */
class Jalali
{
    public const DATE_FORMAT = 'Y/m/d';

    public const DATETIME_FORMAT = 'Y/m/d H:i';

    /**
     * Format a Gregorian date for display in Jalali.
     */
    public static function format(?CarbonInterface $date, string $format = self::DATE_FORMAT): ?string
    {
        if ($date === null) {
            return null;
        }

        return Jalalian::fromCarbon(Carbon::instance($date->toDateTime()))->format($format);
    }

    /**
     * Parse a Jalali string back into a Gregorian Carbon instance.
     *
     * Returns null when the input is blank or not a valid Jalali date, so a
     * bad value becomes an empty field rather than an exception mid-save.
     */
    public static function parse(?string $value, string $format = self::DATE_FORMAT): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        $value = self::toEnglishDigits($value);

        try {
            return Jalalian::fromFormat($format, $value)->toCarbon();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Persian and Arabic-Indic digits arrive from mobile keyboards; the
     * parser only understands ASCII.
     */
    public static function toEnglishDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
