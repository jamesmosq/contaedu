<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('total_facturas_venta')->default(0);
            $table->decimal('monto_total_ventas', 18, 2)->default(0);
            $table->unsignedInteger('total_facturas_compra')->default(0);
            $table->decimal('monto_total_compras', 18, 2)->default(0);
            $table->boolean('balance_cuadrado')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Insertar la fila única de resumen
        DB::table('company_summary')->insert([
            'total_facturas_venta' => 0,
            'monto_total_ventas' => 0,
            'total_facturas_compra' => 0,
            'monto_total_compras' => 0,
            'balance_cuadrado' => true,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_summary');
    }
};
