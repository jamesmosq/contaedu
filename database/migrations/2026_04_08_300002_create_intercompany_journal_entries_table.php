<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intercompany_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intercompany_invoice_id')->constrained()->cascadeOnDelete();
            $table->enum('party', ['seller', 'buyer']);
            $table->string('tenant_id');                // tenant dueño del asiento
            $table->unsignedBigInteger('journal_entry_id'); // ID del asiento en el tenant
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['intercompany_invoice_id', 'party']); // un asiento por parte
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intercompany_journal_entries');
    }
};
