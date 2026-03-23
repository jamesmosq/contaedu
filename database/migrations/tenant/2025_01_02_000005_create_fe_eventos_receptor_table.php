<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fe_eventos_receptor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('fe_facturas')->cascadeOnDelete();
            $table->string('tipo_evento', 30);
            $table->string('cude_evento', 96)->nullable();
            $table->timestamp('fecha_evento');
            $table->text('observaciones')->nullable();
            $table->string('estado', 20)->default('registrado');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->unique(['factura_id', 'tipo_evento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fe_eventos_receptor');
    }
};
