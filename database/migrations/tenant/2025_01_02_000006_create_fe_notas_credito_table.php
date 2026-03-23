<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fe_notas_credito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_origen_id')->constrained('fe_facturas');
            $table->foreignId('resolucion_id')->constrained('fe_resoluciones');

            $table->string('numero_completo', 20);
            $table->string('cude', 96)->nullable()->unique();
            $table->date('fecha_emision');
            $table->time('hora_emision');

            $table->string('codigo_concepto', 2);
            // 1=Devolución parcial bienes, 2=Anulación factura, 3=Rebaja precio,
            // 4=Ajuste por error en NIT, 5=Otros
            $table->text('descripcion_concepto');

            $table->decimal('subtotal', 18, 2);
            $table->decimal('valor_iva', 18, 2)->default(0);
            $table->decimal('total', 18, 2);

            $table->string('estado', 20)->default('generada');
            $table->longText('xml_nota')->nullable();
            $table->longText('xml_application_response')->nullable();

            $table->timestamp('fecha_validacion_dian')->nullable();
            $table->text('mensaje_dian')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fe_notas_credito');
    }
};
