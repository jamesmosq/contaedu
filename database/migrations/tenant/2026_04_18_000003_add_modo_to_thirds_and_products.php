<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('thirds') && ! Schema::hasColumn('thirds', 'modo')) {
            Schema::table('thirds', function (Blueprint $table) {
                $table->string('modo', 10)->default('real')->after('id')->index();
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'modo')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('modo', 10)->default('real')->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('thirds') && Schema::hasColumn('thirds', 'modo')) {
            Schema::table('thirds', function (Blueprint $table) {
                $table->dropColumn('modo');
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'modo')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('products', 'modo');
            });
        }
    }
};
