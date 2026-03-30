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
        Schema::create('platform_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('subject');
            $table->text('body');
            $table->nullableMorphs('related');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['to_user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_notifications');
    }
};
