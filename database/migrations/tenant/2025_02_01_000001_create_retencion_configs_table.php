<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retencion_configs', function (Blueprint $table) {
            $table->id();
            $table->string('concepto', 50)->comment('Valor del enum ConceptoRetencion');
            $table->decimal('porcentaje', 5, 2);
            $table->unsignedBigInteger('cuenta_contable_id')->nullable()->comment('FK a accounts — cuenta pasivo de retención');
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retencion_configs');
    }
};
