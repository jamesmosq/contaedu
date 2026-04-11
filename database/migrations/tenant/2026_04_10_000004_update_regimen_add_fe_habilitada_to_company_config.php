<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_config', function (Blueprint $table) {
            $table->boolean('fe_habilitada')->default(false)->after('regimen');
        });

        // Migrar valores de régimen al nuevo esquema normativo
        DB::table('company_config')
            ->where('regimen', 'simplificado')
            ->update(['regimen' => 'no_responsable_iva', 'fe_habilitada' => false]);

        DB::table('company_config')
            ->whereIn('regimen', ['comun', 'gran_contribuyente'])
            ->update(['regimen' => 'responsable_iva', 'fe_habilitada' => true]);
    }

    public function down(): void
    {
        Schema::table('company_config', function (Blueprint $table) {
            $table->dropColumn('fe_habilitada');
        });

        // Revertir valores de régimen
        DB::table('company_config')
            ->where('regimen', 'no_responsable_iva')
            ->update(['regimen' => 'simplificado']);

        DB::table('company_config')
            ->where('regimen', 'responsable_iva')
            ->update(['regimen' => 'comun']);
    }
};
