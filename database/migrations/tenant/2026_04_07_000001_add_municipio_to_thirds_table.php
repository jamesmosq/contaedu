<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->char('municipio_codigo', 5)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn('municipio_codigo');
        });
    }
};
