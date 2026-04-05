<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // debit_notes: agregar subtotal y tax_amount
        Schema::table('debit_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('debit_notes', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('reason');
            }
            if (! Schema::hasColumn('debit_notes', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            }
        });

        // credit_notes: agregar subtotal y tax_amount
        Schema::table('credit_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('credit_notes', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('reason');
            }
            if (! Schema::hasColumn('credit_notes', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            }
        });

        // credit_note_lines: agregar tax_rate, line_subtotal, line_tax
        Schema::table('credit_note_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('credit_note_lines', 'tax_rate')) {
                $table->integer('tax_rate')->default(0)->after('unit_price');
            }
            if (! Schema::hasColumn('credit_note_lines', 'line_subtotal')) {
                $table->decimal('line_subtotal', 15, 2)->default(0)->after('tax_rate');
            }
            if (! Schema::hasColumn('credit_note_lines', 'line_tax')) {
                $table->decimal('line_tax', 15, 2)->default(0)->after('line_subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('debit_notes', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount']);
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount']);
        });

        Schema::table('credit_note_lines', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'line_subtotal', 'line_tax']);
        });
    }
};
