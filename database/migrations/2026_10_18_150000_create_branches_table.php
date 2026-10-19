<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // deleted_at is part of the key so a soft-deleted branch does not
            // block reusing its name or code. MySQL treats each NULL as
            // distinct, so only one live row per value can exist.
            $table->unique(['name', 'deleted_at']);
            $table->unique(['code', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
