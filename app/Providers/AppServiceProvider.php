<?php

namespace App\Providers;

use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalLine;
use App\Models\Tenant\PurchaseInvoice;
use App\Observers\SummaryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Invoice::observe(SummaryObserver::class);
        PurchaseInvoice::observe(SummaryObserver::class);
        JournalLine::observe(SummaryObserver::class);
    }
}
