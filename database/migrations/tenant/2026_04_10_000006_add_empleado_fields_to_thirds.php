<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            if (! Schema::hasColumn('thirds', 'cargo')) {
                $table->string('cargo')->nullable()->after('active');
            }
            if (! Schema::hasColumn('thirds', 'salario_basico')) {
                $table->decimal('salario_basico', 15, 2)->nullable()->after('cargo');
            }
            if (! Schema::hasColumn('thirds', 'tipo_contrato')) {
                $table->string('tipo_contrato')->nullable()->after('salario_basico')
                    ->comment('indefinido|fijo|obra_labor|prestacion_servicios');
            }
            if (! Schema::hasColumn('thirds', 'procedimiento_retencion')) {
                $table->string('procedimiento_retencion')->nullable()->default('1')->after('tipo_contrato')
                    ->comment('1|2');
            }
            if (! Schema::hasColumn('thirds', 'afp')) {
                $table->string('afp')->nullable()->after('procedimiento_retencion');
            }
            if (! Schema::hasColumn('thirds', 'eps')) {
                $table->string('eps')->nullable()->after('afp');
            }
            if (! Schema::hasColumn('thirds', 'arl')) {
                $table->string('arl')->nullable()->after('eps');
            }
            if (! Schema::hasColumn('thirds', 'fecha_ingreso')) {
                $table->date('fecha_ingreso')->nullable()->after('arl');
            }
            if (! Schema::hasColumn('thirds', 'fecha_retiro')) {
                $table->date('fecha_retiro')->nullable()->after('fecha_ingreso');
            }
            if (! Schema::hasColumn('thirds', 'activo_laboralmente')) {
                $table->boolean('activo_laboralmente')->default(true)->after('fecha_retiro');
            }
        });
    }

    public function down(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn([
                'cargo', 'salario_basico', 'tipo_contrato', 'procedimiento_retencion',
                'afp', 'eps', 'arl', 'fecha_ingreso', 'fecha_retiro', 'activo_laboralmente',
            ]);
        });
    }
};
