<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fe_detalles_factura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('fe_facturas')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('products')->nullOnDelete();

            $table->unsignedSmallInteger('orden');
            $table->string('codigo_producto', 50)->nullable();
            $table->string('codigo_estandar', 50)->nullable();
            $table->string('descripcion', 500);
            $table->string('unidad_medida', 10)->default('94');

            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio_unitario', 18, 4);
            $table->decimal('precio_referencia', 18, 4)->nullable();

            $table->decimal('porcentaje_descuento', 5, 2)->default(0);
            $table->decimal('valor_descuento', 18, 2)->default(0);

            $table->decimal('porcentaje_iva', 5, 2)->default(19);
            $table->decimal('valor_iva', 18, 2)->default(0);
            $table->decimal('porcentaje_ica', 5, 4)->default(0);
            $table->decimal('valor_ica', 18, 2)->default(0);
            $table->decimal('porcentaje_inc', 5, 2)->default(0);
            $table->decimal('valor_inc', 18, 2)->default(0);

            $table->decimal('subtotal_linea', 18, 2);
            $table->decimal('total_linea', 18, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fe_detalles_factura');
    }
};
