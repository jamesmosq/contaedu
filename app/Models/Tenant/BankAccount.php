<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'bank',
        'account_number',
        'account_type',
        'saldo',
        'sobregiro_disponible',
        'sobregiro_usado',
        'sobregiro_periodos',
        'es_principal',
        'recibe_pagos_negocios',
        'activa',
        'bloqueada',
        'cheques_disponibles',
        'cheques_emitidos',
        'fecha_apertura',
    ];

    protected function casts(): array
    {
        return [
            'saldo'                 => 'float',
            'sobregiro_disponible'  => 'float',
            'sobregiro_usado'       => 'float',
            'es_principal'          => 'boolean',
            'recibe_pagos_negocios' => 'boolean',
            'activa'                => 'boolean',
            'bloqueada'             => 'boolean',
            'fecha_apertura'        => 'date',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function statements(): HasMany
    {
        return $this->hasMany(BankStatement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BankDocument::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(BankCheck::class);
    }

    /** Saldo disponible incluyendo sobregiro (solo Banco de Bogotá). */
    public function saldoDisponible(): float
    {
        return $this->saldo + ($this->sobregiro_disponible - $this->sobregiro_usado);
    }

    /** Últimos 4 dígitos del número de cuenta para mostrar en UI. */
    public function ultimosDigitos(): string
    {
        return substr(preg_replace('/[^0-9]/', '', $this->account_number), -4);
    }

    /** Nombre display del banco. */
    public function nombreBanco(): string
    {
        return match ($this->bank) {
            'bancolombia'  => 'Bancolombia',
            'davivienda'   => 'Davivienda',
            'banco_bogota' => 'Banco de Bogotá',
            default        => $this->bank,
        };
    }

    /** Color del banco para UI. */
    public function colorBanco(): string
    {
        return match ($this->bank) {
            'bancolombia'  => 'blue',
            'davivienda'   => 'red',
            'banco_bogota' => 'green',
            default        => 'gray',
        };
    }

    /** Indica si esta cuenta tiene cupo de sobregiro. */
    public function tieneSobregiro(): bool
    {
        return $this->bank === 'banco_bogota'
            && $this->account_type === 'corriente'
            && $this->sobregiro_disponible > 0;
    }
}
