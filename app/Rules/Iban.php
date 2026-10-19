<?php

namespace App\Rules;

use App\Support\Digits;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Iranian IBAN (شبا): "IR" followed by 24 digits, validated with the
 * standard ISO 13616 mod-97 checksum.
 */
class Iban implements ValidationRule
{
    public const LENGTH = 26;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $iban = static::normalise((string) $value);

        if (! preg_match('/^IR\d{24}$/', $iban)) {
            $fail('شماره شبا باید با IR شروع شود و ۲۴ رقم داشته باشد.');

            return;
        }

        if (! $this->passesMod97($iban)) {
            $fail('شماره شبا معتبر نیست. ارقام را دوباره بررسی کنید.');
        }
    }

    /**
     * Uppercase, digits normalised, spaces and a leading "IR-" style prefix
     * removed. Accepts input with or without the IR prefix.
     */
    public static function normalise(string $value): string
    {
        $value = strtoupper(preg_replace('/[\s\-]/', '', (string) Digits::toEnglish($value)) ?? '');

        if (preg_match('/^\d{24}$/', $value)) {
            return 'IR'.$value;
        }

        return $value;
    }

    private function passesMod97(string $iban): bool
    {
        // Move the first four characters to the end, then map letters to
        // numbers (A = 10 ... Z = 35).
        $rearranged = substr($iban, 4).substr($iban, 0, 4);

        $numeric = '';

        foreach (str_split($rearranged) as $character) {
            $numeric .= ctype_alpha($character)
                ? (string) (ord($character) - 55)
                : $character;
        }

        // The number is far wider than PHP integers, so reduce it in chunks.
        $remainder = 0;

        foreach (str_split($numeric, 7) as $chunk) {
            $remainder = (int) (($remainder.$chunk) % 97);
        }

        return $remainder === 1;
    }
}
