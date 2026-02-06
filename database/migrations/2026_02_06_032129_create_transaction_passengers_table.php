<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->string('name');
            $table->string('identity_number');
            $table->string('seat_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_passengers');
    }
};
