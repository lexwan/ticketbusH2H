<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('topup_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topup_id')->constrained('topups');
            $table->foreignId('mitra_id')->constrained('mitra');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->text('description');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_histories');
    }
};
