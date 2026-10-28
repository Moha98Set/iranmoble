<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;

/**
 * Expense rows of the transactions table. See Income for the pattern.
 */
class Expense extends Transaction
{
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('expense', fn (Builder $query) => $query->where('transactions.type', TransactionType::Expense));

        static::creating(function (self $expense): void {
            $expense->type = TransactionType::Expense;
        });
    }
}
