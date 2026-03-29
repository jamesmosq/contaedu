<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Primero hacer la columna nullable
        Schema::table('fe_facturas', function (Blueprint $table) {
            $table->unsignedInteger('numero')->nullable()->change();
        });

        // 2. Luego limpiar borradores con numero=0 (bug anterior)
        DB::table('fe_facturas')->where('numero', 0)->where('estado', 'borrador')->update(['numero' => null]);
    }

    public function down(): void
    {
        // 1. Restaurar NULLs a 0 antes de quitar el nullable
        DB::table('fe_facturas')->whereNull('numero')->update(['numero' => 0]);

        // 2. Volver a NOT NULL
        Schema::table('fe_facturas', function (Blueprint $table) {
            $table->unsignedInteger('numero')->nullable(false)->change();
        });
    }
};
