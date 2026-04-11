<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_account_id')->nullable()->after('account_id')
                ->comment('Cuenta bancaria del módulo banco (opcional). Si se vincula, las bank_transactions se cargan como extracto.');

            // FK con nullOnDelete porque las bank_accounts son del módulo banco (opcional)
            $table->foreign('bank_account_id')
                ->references('id')->on('bank_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });
    }
};
