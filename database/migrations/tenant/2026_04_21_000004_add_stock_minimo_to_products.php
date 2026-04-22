<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'stock_minimo')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('stock_minimo', 10, 2)->default(0)->after('cost_price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'stock_minimo')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('stock_minimo');
            });
        }
    }
};
