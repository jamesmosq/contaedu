<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Hash;

class TenantProvisionService
{
    /**
     * Crea un nuevo tenant (empresa estudiantil) y dispara el pipeline:
     * CreateDatabase → MigrateDatabase → SeedDatabase (PUC colombiano).
     */
    public function provision(array $data): Tenant
    {
        // $data: cedula (id), student_name, company_name, nit_empresa, group_id, password
        return Tenant::create([
            'id'              => $data['cedula'],
            'student_name'    => $data['student_name'],
            'company_name'    => $data['company_name'],
            'nit_empresa'     => $data['nit_empresa'],
            'group_id'        => $data['group_id'],
            'password'        => Hash::make($data['password']),
            'tenancy_db_name' => 'tenant_' . $data['cedula'],
            'active'          => true,
        ]);
    }
}
