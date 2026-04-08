<?php

namespace App\Providers;

use App\Listeners\AutoMigrateTenant;
use App\Listeners\LogSecurityEvent;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalLine;
use App\Models\Tenant\PurchaseInvoice;
use App\Observers\SummaryObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
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

        Event::listen(Login::class, LogSecurityEvent::class);
        Event::listen(Failed::class, LogSecurityEvent::class);
        Event::listen(Logout::class, LogSecurityEvent::class);
        Event::listen(Lockout::class, LogSecurityEvent::class);
        Event::listen(PasswordReset::class, LogSecurityEvent::class);
    }
}
