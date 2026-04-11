<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'tipo',
        'valor',
        'gmf',
        'comision',
        'saldo_despues',
        'descripcion',
        'referencia',
        'banco_destino',
        'cuenta_destino',
        'conciliado',
        'journal_entry_id',
        'intercompany_invoice_id',
        'purchase_invoice_id',
        'fecha_transaccion',
    ];

    protected function casts(): array
    {
        return [
            'valor'           => 'float',
            'gmf'             => 'float',
            'comision'        => 'float',
            'saldo_despues'   => 'float',
            'conciliado'      => 'boolean',
            'fecha_transaccion' => 'date',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    /** Costo total de la transacción (GMF + comisión ACH). */
    public function costoTotal(): float
    {
        return $this->gmf + $this->comision;
    }

    /** Indica si esta transacción genera cargo al saldo (salida de dinero). */
    public function esCargo(): bool
    {
        return in_array($this->tipo, [
            'retiro',
            'transferencia_salida',
            'cheque',
            'pago_proveedor',
            'cuota_manejo',
            'intereses_sobregiro',
            'gmf',
            'comision_ach',
            'sancion_cheque_devuelto',
            'nota_debito',
            'nota_debito_banco',
        ]);
    }
}
