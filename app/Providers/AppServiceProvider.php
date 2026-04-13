<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Event;
use SolutionForest\FilamentTree\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\DocumentType;
use App\Models\Account;
use App\Models\CurrencyPrice;
use App\Models\SystemSetting;
use App\Models\PermissionAccess;
use App\Observers\BranchObserver;
use App\Observers\CurrencyObserver;
use App\Observers\DocumentTypeObserver;
use App\Observers\AccountObserver;
use App\Observers\CurrencyPriceObserver;
use App\Observers\SystemSettingObserver;
use App\Observers\PermissionAccessObserver;
use App\Observers\EntryObserver;
use App\Observers\EntryDetailObserver;
use App\Models\Entry;
use App\Models\EntryDetail;
use App\Models\WalletTransaction;
use App\Events\Accounting\JournalEntryPosted;
use App\Events\Accounting\JournalEntryRolledBack;
use App\Listeners\Accounting\SyncAccountBalances;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Events\Logout;
use App\Models\AccountingSession;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Branch::observe(BranchObserver::class);
        Currency::observe(CurrencyObserver::class);
        DocumentType::observe(DocumentTypeObserver::class);
        Account::observe(AccountObserver::class);
        CurrencyPrice::observe(CurrencyPriceObserver::class);
        SystemSetting::observe(SystemSettingObserver::class);
        PermissionAccess::observe(PermissionAccessObserver::class);

        // Entry::observe(EntryObserver::class);
        // EntryDetail::observe(EntryDetailObserver::class);

        
        Number::useLocale('en');

        // Allow 'admin' user to bypass all permission checks
        Gate::before(function ($user, $ability) {
            return $user->UserName === 'admin' ? true : null;
        });

        Event::listen(
            Logout::class,
            function ($event) {
                $sessionId = Session::get('accounting_session_id');
                if ($sessionId) {
                    AccountingSession::where('legacy_id', $sessionId)
                        ->where('IsEnded', false)
                        ->update([
                            'IsEnded' => true,
                            'EndTime' => now(),
                            'Notes' => 'Logged out manually'
                        ]);
                    Session::forget('accounting_session_id');
                }
            }
        );
    }
}
