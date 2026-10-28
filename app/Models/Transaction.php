<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Base for Income and Expense, which are the same table split by `type`.
 * Nothing should use Transaction directly for writes — the subclasses set
 * the type and scope their queries.
 */
class Transaction extends BaseModel
{
    protected $table = 'transactions';

    protected $fillable = [
        'type',
        'bank_account_id',
        'amount',
        'subject',
        'occurred_at',
        'receipt_path',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => Money::class,
            'occurred_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        /*
         * Recorded from the session, not the form: an operator must not be
         * able to file a transaction under someone else's name.
         */
        static::creating(function (self $transaction): void {
            $transaction->created_by ??= Auth::id();
        });
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasReceipt(): bool
    {
        return filled($this->receipt_path);
    }
}
