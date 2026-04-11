<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->enum('tipo', [
                'certificado',
                'referencia',
                'paz_y_salvo',
                'extracto',
            ]);
            $table->string('pdf_path');
            $table->timestamp('generado_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_documents');
    }
};
