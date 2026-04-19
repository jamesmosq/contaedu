<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_movements') && ! Schema::hasColumn('stock_movements', 'third_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('third_id')->nullable()->after('referencia_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'third_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('third_id');
            });
        }
    }
};
