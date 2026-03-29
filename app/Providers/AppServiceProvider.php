<?php

namespace App\Providers;

use App\Listeners\AutoMigrateTenant;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalLine;
use App\Models\Tenant\PurchaseInvoice;
use App\Observers\SummaryObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyBootstrapped;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Invoice::observe(SummaryObserver::class);
        PurchaseInvoice::observe(SummaryObserver::class);
        JournalLine::observe(SummaryObserver::class);

        Event::listen(TenancyBootstrapped::class, AutoMigrateTenant::class);
    }
}
