<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntercompanyJournalEntry extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'intercompany_journal_entries';

    protected $fillable = [
        'intercompany_invoice_id',
        'party',
        'tenant_id',
        'journal_entry_id',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(IntercompanyInvoice::class, 'intercompany_invoice_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
