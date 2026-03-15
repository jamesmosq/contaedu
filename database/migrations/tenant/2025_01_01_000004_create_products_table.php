<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default('und'); // und|kg|lt|m|caja|par|otro
            $table->decimal('sale_price', 14, 2)->default(0);
            $table->decimal('cost_price', 14, 2)->default(0);
            $table->unsignedBigInteger('inventory_account_id')->nullable();
            $table->unsignedBigInteger('revenue_account_id')->nullable();
            $table->unsignedBigInteger('cogs_account_id')->nullable();
            $table->integer('tax_rate')->default(19); // 0|5|19
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
