<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $connection = 'pgsql';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'codigo_departamento',
        'departamento',
        'codigo_municipio',
        'municipio',
        'label',
    ];
}
