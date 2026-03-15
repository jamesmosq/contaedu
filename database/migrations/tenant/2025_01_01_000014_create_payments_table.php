<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_id')->constrained('thirds');
            $table->date('date');
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('borrador');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices');
            $table->decimal('amount_applied', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('payments');
    }
};
