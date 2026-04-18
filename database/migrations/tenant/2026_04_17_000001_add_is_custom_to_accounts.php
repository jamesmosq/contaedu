<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accounts') && ! Schema::hasColumn('accounts', 'is_custom')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->boolean('is_custom')->default(false)->after('active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('accounts') && Schema::hasColumn('accounts', 'is_custom')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropColumn('is_custom');
            });
        }
    }
};
