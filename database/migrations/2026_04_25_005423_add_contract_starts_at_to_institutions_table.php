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
        Schema::table('institutions', function (Blueprint $table) {
            $table->date('contract_starts_at')->nullable()->after('active');
            $table->boolean('contract_notified_30d')->default(false)->after('contract_expires_at');
            $table->boolean('contract_notified_15d')->default(false)->after('contract_notified_30d');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['contract_starts_at', 'contract_notified_30d', 'contract_notified_15d']);
        });
    }
};
