<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 30);        // FixedAssetCategory enum
            $table->date('acquisition_date');
            $table->decimal('cost', 15, 2);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->unsignedSmallInteger('useful_life_months');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->date('last_depreciation_date')->nullable();
            $table->string('status', 30)->default('activo');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
