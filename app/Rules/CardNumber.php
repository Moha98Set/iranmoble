<?php

namespace App\Rules;

use App\Support\Digits;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 16 digits passing the Luhn checksum — the same check a POS terminal runs.
 * It catches almost every single-digit typo and most transpositions, which
 * matters here because a wrong card number sends money to a stranger.
 */
class CardNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $number = Digits::onlyNumbers((string) $value);

        if (strlen($number) !== 16) {
            $fail('شماره کارت باید ۱۶ رقم باشد.');

            return;
        }

        if (! $this->passesLuhn($number)) {
            $fail('شماره کارت معتبر نیست. ارقام را دوباره بررسی کنید.');
        }
    }

    private function passesLuhn(string $number): bool
    {
        $sum = 0;

        foreach (array_reverse(str_split($number)) as $index => $digit) {
            $digit = (int) $digit;

            if ($index % 2 === 1) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}
