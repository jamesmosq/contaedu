<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debit_notes', function (Blueprint $table) {
            // Descomponer `amount` en subtotal + tax_amount + total
            // El campo `amount` original pasa a representar el total (se renombra conceptualmente)
            $table->decimal('subtotal', 15, 2)->default(0)->after('reason')
                ->comment('Subtotal antes de IVA');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal')
                ->comment('Valor del IVA');

            // `amount` pasa a ser el total (subtotal + tax_amount)
            // Se mantiene para retrocompatibilidad; nuevos registros usan amount = subtotal + tax_amount
        });

        // Sincronizar registros existentes: amount ya es el total, subtotal = amount, tax_amount = 0
        DB::statement('UPDATE debit_notes SET subtotal = amount, tax_amount = 0 WHERE subtotal = 0 AND amount > 0');
    }

    public function down(): void
    {
        Schema::table('debit_notes', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount']);
        });
    }
};
