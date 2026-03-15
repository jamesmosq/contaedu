<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary(); // cédula del estudiante, ej: cc1023456789
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('student_name');
            $table->string('company_name');
            $table->string('nit_empresa')->unique();
            $table->string('password');
            $table->string('tenancy_db_name'); // nombre del schema postgresql
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->json('data')->nullable(); // requerido por stancl/tenancy
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
