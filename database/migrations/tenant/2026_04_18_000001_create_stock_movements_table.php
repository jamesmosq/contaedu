<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('modo', 10)->default('real')->index();
            $table->unsignedBigInteger('product_id');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('qty', 15, 4);
            $table->decimal('costo_unitario', 15, 4)->default(0);
            $table->decimal('costo_total', 15, 2)->default(0);
            $table->string('referencia_tipo', 30)->nullable(); // factura | compra | ajuste
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->decimal('saldo_qty', 15, 4)->default(0);   // stock acumulado tras el movimiento
            $table->decimal('saldo_valor', 15, 2)->default(0); // valor acumulado tras el movimiento
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'modo', 'fecha']);
            $table->index(['referencia_tipo', 'referencia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
