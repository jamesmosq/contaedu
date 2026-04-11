<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoice_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_invoice_lines', 'cuenta_gasto_codigo')) {
                $table->string('cuenta_gasto_codigo')->nullable()->after('line_total')
                    ->comment('Código PUC de la cuenta de gasto/costo (solo facturas directas)');
            }
            if (! Schema::hasColumn('purchase_invoice_lines', 'cuenta_gasto_nombre')) {
                $table->string('cuenta_gasto_nombre')->nullable()->after('cuenta_gasto_codigo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoice_lines', function (Blueprint $table) {
            $table->dropColumn(['cuenta_gasto_codigo', 'cuenta_gasto_nombre']);
        });
    }
};
