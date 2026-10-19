<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('bank_name');
            $table->string('account_holder');
            $table->string('account_number')->nullable();

            // Stored as plain digits; formatting happens on display.
            $table->string('card_number', 16)->nullable();
            $table->string('iban', 26)->nullable();

            // The bank's own branch — not a Branch of this company.
            $table->string('bank_branch')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // deleted_at in the key so a soft-deleted account does not block
            // re-entering the same card or IBAN later.
            $table->unique(['card_number', 'deleted_at']);
            $table->unique(['iban', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
