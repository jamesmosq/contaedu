<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intercompany_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intercompany_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('descripcion');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('iva', 15, 2)->default(0);
            $table->unsignedTinyInteger('porcentaje_iva')->default(0);
            $table->string('cuenta_ingreso_codigo', 10)->nullable(); // PUC del vendedor (4xxx)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intercompany_invoice_items');
    }
};
