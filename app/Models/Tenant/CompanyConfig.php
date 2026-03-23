<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CompanyConfig extends Model
{
    protected $table = 'company_config';

    protected $fillable = [
        'nit',
        'razon_social',
        'regimen',
        'ciiu_code',
        'ciiu_description',
        'direccion',
        'telefono',
        'email',
        'logo_path',
        'prefijo_factura',
        'resolucion_dian',
    ];
}
