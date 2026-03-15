<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('total');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
        });

        Schema::table('credit_note_lines', function (Blueprint $table) {
            $table->integer('tax_rate')->default(0)->after('line_total');
            $table->decimal('line_subtotal', 15, 2)->default(0)->after('tax_rate');
            $table->decimal('line_tax', 15, 2)->default(0)->after('line_subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount']);
        });

        Schema::table('credit_note_lines', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'line_subtotal', 'line_tax']);
        });
    }
};
