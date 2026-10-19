<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();

            // String, not integer: real unit labels include "4B", "همکف"
            // and "پنت‌هاوس".
            $table->string('unit_number');
            $table->smallInteger('floor')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('capacity')->nullable();

            // Whole Toman, cast through App\Casts\Money.
            $table->unsignedBigInteger('base_price')->nullable();

            $table->text('description')->nullable();

            // Out of service (renovation, withdrawn). Occupancy is NOT stored
            // here — it is derived from reservations, because a unit is only
            // free relative to a date range.
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'unit_number', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
