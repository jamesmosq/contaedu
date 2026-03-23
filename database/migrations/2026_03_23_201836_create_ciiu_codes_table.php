<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciiu_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->string('name');
            $table->string('section', 5)->nullable();
            $table->string('division', 10)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciiu_codes');
    }
};
