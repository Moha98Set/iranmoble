<?php

namespace App\Models;

use App\Rules\Iban;
use App\Support\Digits;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BankAccount extends BaseModel
{
    protected $fillable = [
        'title',
        'bank_name',
        'account_holder',
        'account_number',
        'card_number',
        'iban',
        'bank_branch',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Digits are normalised on the way in, so a value typed on a Persian
     * keyboard is stored the same as one typed on an English keyboard and
     * the unique index actually catches duplicates.
     */
    protected function cardNumber(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => blank($value)
            ? null
            : Digits::onlyNumbers($value));
    }

    protected function iban(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => blank($value)
            ? null
            : Iban::normalise($value));
    }

    protected function accountNumber(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => blank($value)
            ? null
            : (string) Digits::toEnglish($value));
    }

    /**
     * 6219-8610-1234-5678
     */
    public function getFormattedCardNumberAttribute(): ?string
    {
        return blank($this->card_number)
            ? null
            : implode('-', str_split($this->card_number, 4));
    }

    /**
     * Last four digits only, for lists where the full number is noise.
     */
    public function getMaskedCardNumberAttribute(): ?string
    {
        return blank($this->card_number)
            ? null
            : '•••• '.substr($this->card_number, -4);
    }
}
