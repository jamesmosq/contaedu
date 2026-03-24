<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_group', function (Blueprint $table) {
            $table->string('demo_tenant_id');
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();

            $table->primary(['demo_tenant_id', 'group_id']);

            $table->foreign('demo_tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_group');
    }
};
