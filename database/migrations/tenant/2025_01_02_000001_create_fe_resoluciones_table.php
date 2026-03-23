<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fe_resoluciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero_resolucion', 50);
            $table->string('prefijo', 10)->nullable();
            $table->unsignedInteger('numero_desde');
            $table->unsignedInteger('numero_hasta');
            $table->unsignedInteger('numero_actual');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->string('clave_tecnica', 255);
            $table->string('ambiente', 2)->default('02');
            $table->boolean('activa')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fe_resoluciones');
    }
};
