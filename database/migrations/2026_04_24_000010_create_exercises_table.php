<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('instructions')->nullable();
            $table->enum('type', [
                'factura_venta',
                'factura_compra',
                'asiento_manual',
                'registro_tercero',
                'registro_producto',
                'pago_proveedor',
            ]);
            $table->decimal('monto_minimo', 15, 2)->nullable();
            $table->string('cuenta_puc_requerida', 10)->nullable();
            $table->tinyInteger('puntos')->default(10);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('exercise_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('exercise_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('exercise_assignments')->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('result', ['aprobado', 'parcial', 'no_cumple', 'pendiente'])->default('pendiente');
            $table->jsonb('verification_detail')->nullable();
            $table->timestamps();
            $table->unique(['assignment_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_completions');
        Schema::dropIfExists('exercise_assignments');
        Schema::dropIfExists('exercises');
    }
};
