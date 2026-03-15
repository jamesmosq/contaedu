<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_config', function (Blueprint $table) {
            $table->id();
            $table->string('nit');
            $table->string('razon_social');
            $table->string('regimen')->default('simplificado'); // simplificado|comun|gran_contribuyente
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('prefijo_factura')->default('FV');
            $table->string('resolucion_dian')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_config');
    }
};
