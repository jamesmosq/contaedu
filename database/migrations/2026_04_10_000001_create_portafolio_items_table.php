<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portafolio_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['producto', 'servicio']);
            $table->decimal('precio', 15, 2);
            $table->enum('iva', ['19', '5', '0'])->default('19');
            $table->string('cuenta_ingreso_codigo');
            $table->string('cuenta_ingreso_nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portafolio_items');
    }
};
