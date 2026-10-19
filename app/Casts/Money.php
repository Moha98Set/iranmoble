<?php

namespace App\Casts;

use App\Support\Digits;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Money is stored as a whole number of Toman. No decimals, no floats —
 * floats lose Rial-level precision once totals get large.
 *
 * The cast keeps the model value an int; formatting for display is done by
 * the accessor on the model or by the Filament column, not here, so the
 * value stays usable in sums and comparisons.
 *
 * @implements CastsAttributes<int|null, int|string|null>
 */
class Money implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return $value === null ? null : (int) $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Accept input that came back from a formatted field: "۱٬۲۰۰٬۰۰۰" or "1,200,000".
        if (is_string($value)) {
            $negative = str_starts_with(trim($value), '-');
            $value = Digits::onlyNumbers($value);

            if ($value === '') {
                return null;
            }

            $value = $negative ? "-{$value}" : $value;
        }

        return (int) $value;
    }
}
