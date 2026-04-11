<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // portafolio_item_id — solo agregar FK si la columna ya existe pero sin constraint,
        // o agregar la columna si no existe aún.
        if (! Schema::hasColumn('intercompany_invoice_items', 'portafolio_item_id')) {
            Schema::table('intercompany_invoice_items', function (Blueprint $table) {
                $table->foreignId('portafolio_item_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('portafolio_items')
                    ->nullOnDelete();
            });
        }

        // gasto_code_comprador: cuenta de gasto que elige el comprador al crear el pedido
        if (! Schema::hasColumn('intercompany_invoices', 'gasto_code_comprador')) {
            Schema::table('intercompany_invoices', function (Blueprint $table) {
                $table->string('gasto_code_comprador')->nullable()->after('concepto');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('intercompany_invoice_items', 'portafolio_item_id')) {
            Schema::table('intercompany_invoice_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('portafolio_item_id');
            });
        }

        if (Schema::hasColumn('intercompany_invoices', 'gasto_code_comprador')) {
            Schema::table('intercompany_invoices', function (Blueprint $table) {
                $table->dropColumn('gasto_code_comprador');
            });
        }
    }
};
