<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->boolean('is_global')->default(false)->after('active');
            $table->foreignId('cloned_from_id')->nullable()->constrained('exercises')->nullOnDelete()->after('is_global');
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropForeign(['cloned_from_id']);
            $table->dropColumn(['is_global', 'cloned_from_id']);
        });
    }
};
