<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('topup_histories', 'balance_after')) {
            Schema::table('topup_histories', function (Blueprint $table) {
                $table->decimal('balance_after', 15, 2)->nullable()->after('balance_before');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('topup_histories', 'balance_after')) {
            Schema::table('topup_histories', function (Blueprint $table) {
                $table->dropColumn('balance_after');
            });
        }
    }
};
