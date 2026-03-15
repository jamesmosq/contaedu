<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thirds', function (Blueprint $table) {
            $table->id();
            $table->string('document_type'); // cc|nit|ce|pasaporte
            $table->string('document')->unique();
            $table->string('name');
            $table->string('type'); // cliente|proveedor|ambos
            $table->string('regimen')->default('simplificado'); // simplificado|comun
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thirds');
    }
};
