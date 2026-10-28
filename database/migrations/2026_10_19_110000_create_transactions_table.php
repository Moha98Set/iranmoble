<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Income and expense share one table. The panel still shows two separate
 * resources, but every financial report becomes a single query instead of a
 * union of two.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('bank_account_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();

            // Whole Toman, cast through App\Casts\Money.
            $table->unsignedBigInteger('amount');

            $table->string('subject');
            $table->date('occurred_at');

            // Relative path on the private disk. Nullable: a transaction can
            // be recorded before the receipt is in hand.
            $table->string('receipt_path')->nullable();

            $table->text('description')->nullable();

            // The operator who recorded it. Set from the authenticated user,
            // never from the form. nullOnDelete so removing a user does not
            // take their financial records with them.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Every list and report filters by type and then by date.
            $table->index(['type', 'occurred_at']);
            $table->index('bank_account_id');

            // The operator scope in the spec filters on this.
            $table->index(['created_by', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
