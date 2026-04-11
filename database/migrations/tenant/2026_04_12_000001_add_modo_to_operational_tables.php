<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'journal_entries',
        'invoices',
        'purchase_invoices',
        'purchase_orders',
        'payments',
        'cash_receipts',
        'credit_notes',
        'debit_notes',
        'fixed_assets',
        'bank_reconciliations',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'modo')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('modo', 10)->default('real')->after('id');
                    $t->index('modo');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'modo')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('modo');
                });
            }
        }
    }
};
