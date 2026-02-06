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
        Schema::table('mitra', function (Blueprint $table) {
            $table->string('code')->unique()->after('id');
            $table->string('email')->after('name');
            $table->string('phone')->after('email');
            $table->enum('status', ['pending', 'active', 'rejected'])->default('pending')->after('phone');
            $table->decimal('balance', 15, 2)->default(0)->after('status');
            $table->dropColumn('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra', function (Blueprint $table) {
            $table->dropColumn(['code', 'email', 'phone', 'status', 'balance']);
            $table->timestamps();
        });
    }
};
