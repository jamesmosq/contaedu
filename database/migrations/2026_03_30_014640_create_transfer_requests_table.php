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
        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requesting_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('target_group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('transfer_mode'); // keep|reset|fresh
            $table->string('status')->default('pending'); // pending|approved|rejected|claimed|cancelled
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_requests');
    }
};
