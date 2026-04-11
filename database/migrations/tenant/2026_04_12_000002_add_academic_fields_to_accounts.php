<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accounts') && ! Schema::hasColumn('accounts', 'descripcion')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->text('descripcion')->nullable()->after('active');
                $table->text('dinamica_debe')->nullable()->after('descripcion');
                $table->text('dinamica_haber')->nullable()->after('dinamica_debe');
                $table->text('ejemplo')->nullable()->after('dinamica_haber');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('accounts') && Schema::hasColumn('accounts', 'descripcion')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropColumn(['descripcion', 'dinamica_debe', 'dinamica_haber', 'ejemplo']);
            });
        }
    }
};
