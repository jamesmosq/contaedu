<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->unsignedTinyInteger('sobregiro_periodos')->default(0)->after('sobregiro_usado')
                ->comment('Períodos consecutivos con sobregiro sin pagar. Al llegar a 2 la cuenta se bloquea.');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('sobregiro_periodos');
        });
    }
};
