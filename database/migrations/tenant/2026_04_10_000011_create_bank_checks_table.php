<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->string('numero_cheque', 10);
            $table->string('beneficiario');
            $table->decimal('valor', 15, 2);
            $table->date('fecha_emision');
            $table->date('fecha_cobro')->nullable();
            $table->enum('estado', ['emitido', 'cobrado', 'devuelto', 'anulado'])->default('emitido');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_checks');
    }
};
