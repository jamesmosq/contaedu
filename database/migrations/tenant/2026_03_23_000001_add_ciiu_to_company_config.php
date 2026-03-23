<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_config', function (Blueprint $table) {
            $table->string('ciiu_code', 6)->nullable()->after('regimen');
            $table->string('ciiu_description')->nullable()->after('ciiu_code');
        });
    }

    public function down(): void
    {
        Schema::table('company_config', function (Blueprint $table) {
            $table->dropColumn(['ciiu_code', 'ciiu_description']);
        });
    }
};
