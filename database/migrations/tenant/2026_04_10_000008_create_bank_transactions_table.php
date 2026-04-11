<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->enum('tipo', [
                'consignacion',
                'retiro',
                'transferencia_salida',
                'transferencia_entrada',
                'nota_debito',
                'nota_credito',
                'cheque',
                'cheque_devuelto',
                'pago_proveedor',
                'cobro_cliente',
                'cuota_manejo',
                'intereses_ahorros',
                'intereses_sobregiro',
                'gmf',
                'comision_ach',
                'sancion_cheque_devuelto',
            ]);
            $table->decimal('valor', 15, 2);
            $table->decimal('gmf', 15, 2)->default(0);
            $table->decimal('comision', 15, 2)->default(0);
            $table->decimal('saldo_despues', 15, 2);
            $table->string('descripcion');
            $table->string('referencia', 50)->nullable();
            $table->string('banco_destino', 30)->nullable();
            $table->string('cuenta_destino', 30)->nullable();
            $table->boolean('conciliado')->default(false);
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            // intercompany_invoice_id referencia a tabla central — sin FK constraint
            $table->unsignedBigInteger('intercompany_invoice_id')->nullable();
            $table->foreignId('purchase_invoice_id')->nullable()->constrained('purchase_invoices')->nullOnDelete();
            $table->date('fecha_transaccion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
