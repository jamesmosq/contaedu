<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->char('codigo', 5)->primary();   // ej: "05001"
            $table->char('codigo_departamento', 2); // ej: "05"
            $table->string('departamento', 80);     // ej: "ANTIOQUIA"
            $table->char('codigo_municipio', 3);    // ej: "001"
            $table->string('municipio', 80);        // ej: "MEDELLIN"
            $table->string('label', 120);           // ej: "ANTIOQUIA 05 - MEDELLIN 001"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
