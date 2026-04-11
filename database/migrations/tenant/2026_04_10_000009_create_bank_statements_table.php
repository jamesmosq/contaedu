<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->decimal('saldo_inicial', 15, 2);
            $table->decimal('total_debitos', 15, 2)->default(0);
            $table->decimal('total_creditos', 15, 2)->default(0);
            $table->decimal('saldo_final', 15, 2);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statements');
    }
};
