<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'درآمد',
            self::Expense => 'هزینه',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Income => 'success',
            self::Expense => 'danger',
        };
    }

    /**
     * Sign used when summing a mixed list into a net figure.
     */
    public function sign(): int
    {
        return $this === self::Income ? 1 : -1;
    }
}
