<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_id')->constrained('thirds');
            $table->date('date');
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('borrador');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('cash_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained();
            $table->decimal('amount_applied', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_receipt_items');
        Schema::dropIfExists('cash_receipts');
    }
};
