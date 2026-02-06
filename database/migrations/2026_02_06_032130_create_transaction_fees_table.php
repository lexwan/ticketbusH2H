<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('mitra_id')->constrained('mitra')->onDelete('cascade');
            $table->enum('fee_type', ['percent', 'flat']);
            $table->decimal('fee_value', 10, 2);
            $table->decimal('fee_amount', 15, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_fees');
    }
};
