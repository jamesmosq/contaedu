<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intercompany_invoices', function (Blueprint $table) {
            $table->decimal('retencion_iva', 15, 2)->default(0)->after('retencion_fuente');
            $table->decimal('retencion_ica', 15, 2)->default(0)->after('retencion_iva');
        });
    }

    public function down(): void
    {
        Schema::table('intercompany_invoices', function (Blueprint $table) {
            $table->dropColumn(['retencion_iva', 'retencion_ica']);
        });
    }
};
