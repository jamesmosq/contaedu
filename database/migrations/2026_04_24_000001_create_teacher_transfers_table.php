<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->string('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->string('target_name');
            $table->decimal('amount', 15, 2);
            $table->string('description', 200);
            $table->enum('mode', ['grupal', 'individual']);
            $table->unsignedSmallInteger('students_reached')->default(0);
            $table->unsignedSmallInteger('students_skipped')->default(0);
            $table->timestamps();

            $table->index(['teacher_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_transfers');
    }
};
