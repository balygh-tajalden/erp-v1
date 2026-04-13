<?php

namespace App\Observers;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class AccountObserver
{
    protected function clearLookupCache(): void
    {
        // مسح جميع القوائم المنسدلة المرتبطة بالحسابات
        Cache::forget('lookup:accounts');
        Cache::forget('lookup:accounts:all');
        Cache::forget('lookup:accounts:detailed');
        Cache::forget('lookup:accounts:children');
        Cache::forget('lookup:accounts:cashboxes');
        Cache::forget('lookup:parent_accounts_for_type_1');
        Cache::forget('lookup:parent_accounts_for_type_2');
    }
    public function saved(Account $account): void
    {
        $this->clearLookupCache();

    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Account "restored" event.
     */
    public function restored(Account $account): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Account "force deleted" event.
     */
    public function forceDeleted(Account $account): void
    {
        $this->clearLookupCache();
    }
}
