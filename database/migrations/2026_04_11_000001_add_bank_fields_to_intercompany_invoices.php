<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intercompany_invoices', function (Blueprint $table) {
            // Cuenta bancaria del comprador (cross-schema — sin FK constraint)
            $table->unsignedBigInteger('buyer_bank_account_id')->nullable()->after('gasto_code_comprador');
            $table->string('buyer_bank', 30)->nullable()->after('buyer_bank_account_id');

            // Cuenta bancaria del vendedor (cross-schema — sin FK constraint)
            $table->unsignedBigInteger('seller_bank_account_id')->nullable()->after('buyer_bank');
            $table->string('seller_bank', 30)->nullable()->after('seller_bank_account_id');

            // Costos bancarios calculados al momento del pago
            $table->decimal('gmf_total', 12, 2)->nullable()->after('seller_bank');
            $table->decimal('comision_ach', 12, 2)->nullable()->after('gmf_total');
        });
    }

    public function down(): void
    {
        Schema::table('intercompany_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_bank_account_id',
                'buyer_bank',
                'seller_bank_account_id',
                'seller_bank',
                'gmf_total',
                'comision_ach',
            ]);
        });
    }
};
