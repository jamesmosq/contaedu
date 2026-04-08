<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@contaedu.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Superadmin,
        ]);

        // Institución
        $institution = Institution::create([
            'name' => 'Universidad Nacional de Colombia',
            'nit' => '899999063',
            'city' => 'Bogotá',
            'active' => true,
        ]);

        // Docente
        $teacher = User::create([
            'name' => 'Carlos Martínez',
            'email' => 'docente@contaedu.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Teacher,
        ]);

        // Grupo
        $group = Group::create([
            'institution_id' => $institution->id,
            'teacher_id' => $teacher->id,
            'name' => 'Contabilidad 101',
            'period' => '2025-1',
            'active' => true,
        ]);

        // Asignar docente al grupo
        $teacher->update(['group_id' => $group->id]);

        // Estudiantes (tenants) — se crea el schema automáticamente
        $students = [
            [
                'id' => 'cc1023456789',
                'student_name' => 'Ana García',
                'company_name' => 'García Distribuciones S.A.S.',
                'nit_empresa' => '900111222',
            ],
            [
                'id' => 'cc1098765432',
                'student_name' => 'Luis Pérez',
                'company_name' => 'Pérez Comercial E.U.',
                'nit_empresa' => '900333444',
            ],
            [
                'id' => 'cc1055544433',
                'student_name' => 'María Rodríguez',
                'company_name' => 'Rodríguez & Asociados S.A.S.',
                'nit_empresa' => '900555666',
            ],
        ];

        foreach ($students as $student) {
            Tenant::create([
                'id' => $student['id'],
                'group_id' => $group->id,
                'student_name' => $student['student_name'],
                'company_name' => $student['company_name'],
                'nit_empresa' => $student['nit_empresa'],
                'password' => Hash::make('password'),
                'tenancy_db_name' => 'tenant_'.$student['id'],
                'active' => true,
            ]);
        }

        // Códigos CIIU colombianos (tabla central)
        $this->call(CiiuSeeder::class);

        // Municipios DIAN (tabla central)
        $this->call(MunicipiosSeeder::class);

        // Sembrar datos demo completos en cada empresa (ciclo contable completo)
        $this->call(DemoDataSeeder::class);
    }
}
