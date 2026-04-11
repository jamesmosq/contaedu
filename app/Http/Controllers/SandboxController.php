<?php

namespace App\Http\Controllers;

use App\Models\Tenant\CashReceipt;
use App\Models\Tenant\CreditNote;
use App\Models\Tenant\DebitNote;
use App\Models\Tenant\FixedAsset;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PurchaseInvoice;
use App\Models\Tenant\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class SandboxController extends Controller
{
    /**
     * Elimina todos los datos del sandbox del tenant actual.
     * Los datos de MI EMPRESA (modo=real) quedan intactos.
     */
    public function reset()
    {
        DB::transaction(function () {
            $entryIds = JournalEntry::where('modo', 'sandbox')->pluck('id');
            JournalLine::whereIn('journal_entry_id', $entryIds)->delete();
            JournalEntry::where('modo', 'sandbox')->delete();

            Invoice::where('modo', 'sandbox')->delete();
            PurchaseInvoice::where('modo', 'sandbox')->delete();
            PurchaseOrder::where('modo', 'sandbox')->delete();
            Payment::where('modo', 'sandbox')->delete();
            CashReceipt::where('modo', 'sandbox')->delete();
            CreditNote::where('modo', 'sandbox')->delete();
            DebitNote::where('modo', 'sandbox')->delete();
            FixedAsset::where('modo', 'sandbox')->delete();
        });

        return redirect()
            ->route('sandbox.dashboard')
            ->with('success', 'Empresa de aprendizaje reiniciada. Puedes comenzar de nuevo.');
    }
}
