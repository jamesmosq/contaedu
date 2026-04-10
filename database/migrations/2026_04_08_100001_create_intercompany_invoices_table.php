<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intercompany_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('seller_tenant_id');
            $table->string('buyer_tenant_id');
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('consecutive', 12);
            $table->enum('status', ['pendiente', 'aceptada', 'rechazada'])->default('pendiente');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('iva', 15, 2)->default(0);
            $table->decimal('retencion_fuente', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->text('concepto');
            $table->text('rechazo_motivo')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('seller_tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('buyer_tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intercompany_invoices');
    }
};
