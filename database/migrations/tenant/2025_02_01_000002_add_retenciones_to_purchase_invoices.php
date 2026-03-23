<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->string('retencion_concepto', 50)->nullable()->after('notes')
                ->comment('Valor del enum ConceptoRetencion aplicado');
            $table->decimal('retefte_base', 15, 2)->default(0)->after('retencion_concepto')
                ->comment('Base gravable sobre la que se calculó RteFte');
            $table->decimal('retefte_porcentaje', 5, 2)->default(0)->after('retefte_base')
                ->comment('Porcentaje de RteFte aplicado');
            $table->decimal('retefte_valor', 15, 2)->default(0)->after('retefte_porcentaje')
                ->comment('Valor retenido por concepto de Retención en la Fuente');
            $table->decimal('reteiva_valor', 15, 2)->default(0)->after('retefte_valor')
                ->comment('Valor retenido por concepto de Reteiva (15% del IVA)');
            $table->decimal('reteica_valor', 15, 2)->default(0)->after('reteiva_valor')
                ->comment('Valor retenido por concepto de Reteica');
            $table->decimal('total_retenciones', 15, 2)->default(0)->after('reteica_valor')
                ->comment('Suma de retefte + reteiva + reteica');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'retencion_concepto',
                'retefte_base',
                'retefte_porcentaje',
                'retefte_valor',
                'reteiva_valor',
                'reteica_valor',
                'total_retenciones',
            ]);
        });
    }
};
