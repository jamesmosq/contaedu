<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('student_tenant_id');
            $table->string('demo_tenant_id');
            $table->timestamp('accessed_at');

            $table->unique(['student_tenant_id', 'demo_tenant_id']);

            $table->foreign('student_tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('demo_tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_access_logs');
    }
};
