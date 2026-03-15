<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('type'); // activo|pasivo|patrimonio|ingreso|costo|gasto|orden
            $table->string('nature'); // debito|credito
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedTinyInteger('level'); // 1-4
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
