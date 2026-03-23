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
        Schema::table('tenants', function (Blueprint $table) {
            // 'student' = empresa real | 'demo' = empresa de demostración del docente
            $table->string('type')->default('student')->after('id');

            // Docente propietario (null para empresas de estudiantes)
            $table->foreignId('teacher_id')
                ->nullable()
                ->after('group_id')
                ->constrained('users')
                ->nullOnDelete();

            // Visible para estudiantes del docente
            $table->boolean('published')->default(false)->after('active');

            // Sector comercial de la empresa demo
            $table->string('sector')->nullable()->after('published');

            // group_id pasa a nullable (empresas demo no pertenecen a un grupo)
            $table->foreignId('group_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn(['type', 'published', 'sector']);
            $table->foreignId('group_id')->nullable(false)->change();
        });
    }
};
