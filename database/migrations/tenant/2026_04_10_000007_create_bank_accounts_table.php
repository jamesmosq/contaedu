<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('bank', ['bancolombia', 'davivienda', 'banco_bogota']);
            $table->string('account_number', 30);
            $table->enum('account_type', ['corriente', 'ahorros'])->default('corriente');
            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('sobregiro_disponible', 15, 2)->default(0);
            $table->decimal('sobregiro_usado', 15, 2)->default(0);
            $table->boolean('es_principal')->default(false);
            $table->boolean('activa')->default(true);
            $table->boolean('bloqueada')->default(false);
            $table->integer('cheques_disponibles')->nullable();
            $table->integer('cheques_emitidos')->default(0);
            $table->date('fecha_apertura');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
