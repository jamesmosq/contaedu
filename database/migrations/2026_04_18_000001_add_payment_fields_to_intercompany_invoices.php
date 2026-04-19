<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('intercompany_invoices')) {
            Schema::table('intercompany_invoices', function (Blueprint $table) {
                if (! Schema::hasColumn('intercompany_invoices', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('accepted_at');
                }
                if (! Schema::hasColumn('intercompany_invoices', 'collected_at')) {
                    $table->timestamp('collected_at')->nullable()->after('paid_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('intercompany_invoices')) {
            Schema::table('intercompany_invoices', function (Blueprint $table) {
                $table->dropColumnIfExists('paid_at');
                $table->dropColumnIfExists('collected_at');
            });
        }
    }
};
