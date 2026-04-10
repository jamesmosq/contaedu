<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL enum → CHECK constraint. Drop y recrear con 'anulada'.
        DB::statement("ALTER TABLE intercompany_invoices DROP CONSTRAINT IF EXISTS intercompany_invoices_status_check");
        DB::statement("ALTER TABLE intercompany_invoices ADD CONSTRAINT intercompany_invoices_status_check CHECK (status IN ('pendiente','aceptada','rechazada','anulada'))");

        Schema::table('intercompany_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('anulada_by')->nullable()->after('accepted_at');
            $table->timestamp('anulada_at')->nullable()->after('anulada_by');
            $table->text('anulacion_motivo')->nullable()->after('anulada_at');

            $table->foreign('anulada_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intercompany_invoices', function (Blueprint $table) {
            $table->dropForeign(['anulada_by']);
            $table->dropColumn(['anulada_by', 'anulada_at', 'anulacion_motivo']);
        });

        DB::statement("ALTER TABLE intercompany_invoices DROP CONSTRAINT IF EXISTS intercompany_invoices_status_check");
        DB::statement("ALTER TABLE intercompany_invoices ADD CONSTRAINT intercompany_invoices_status_check CHECK (status IN ('pendiente','aceptada','rechazada'))");
    }
};
