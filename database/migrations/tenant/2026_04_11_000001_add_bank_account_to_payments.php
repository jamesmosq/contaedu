<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // ID de la cuenta bancaria desde donde se pagó (null = pago desde caja)
            $table->unsignedBigInteger('bank_account_id')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('bank_account_id');
        });
    }
};
