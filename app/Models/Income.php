<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;

/**
 * Income rows of the transactions table. The global scope keeps expenses out
 * of every query, and the creating hook stamps the type so no caller has to
 * remember it.
 */
class Income extends Transaction
{
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('income', fn (Builder $query) => $query->where('transactions.type', TransactionType::Income));

        static::creating(function (self $income): void {
            $income->type = TransactionType::Income;
        });
    }
}
