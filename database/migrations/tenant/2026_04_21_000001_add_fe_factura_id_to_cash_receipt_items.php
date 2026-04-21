<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cash_receipt_items') && ! Schema::hasColumn('cash_receipt_items', 'fe_factura_id')) {
            Schema::table('cash_receipt_items', function (Blueprint $table) {
                $table->unsignedBigInteger('fe_factura_id')->nullable()->after('invoice_id');
                $table->foreign('fe_factura_id')->references('id')->on('fe_facturas')->nullOnDelete();
            });

            // invoice_id ya existía como NOT NULL — hacerlo nullable para soportar cobros de FE
            Schema::table('cash_receipt_items', function (Blueprint $table) {
                $table->unsignedBigInteger('invoice_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cash_receipt_items') && Schema::hasColumn('cash_receipt_items', 'fe_factura_id')) {
            Schema::table('cash_receipt_items', function (Blueprint $table) {
                $table->dropForeign(['fe_factura_id']);
                $table->dropColumn('fe_factura_id');
                $table->unsignedBigInteger('invoice_id')->nullable(false)->change();
            });
        }
    }
};
