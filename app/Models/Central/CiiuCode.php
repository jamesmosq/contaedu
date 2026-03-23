<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class CiiuCode extends Model
{
    protected $fillable = ['code', 'name', 'section', 'division', 'active'];

    public function getConnectionName(): string
    {
        return config('tenancy.database.central_connection', 'pgsql');
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function label(): string
    {
        return $this->code.' — '.$this->name;
    }
}
