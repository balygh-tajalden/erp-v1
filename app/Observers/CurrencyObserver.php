<?php

namespace App\Observers;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyObserver
{
    protected function clearLookupCache(): void
    {
        Cache::forget('lookup:currencies');
        Cache::forget('lookup:main_currency_id');
    }


    /**
     * Handle the Currency "created" event.
     */
    public function created(Currency $currency): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Currency "updated" event.
     */
    public function updated(Currency $currency): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Currency "deleted" event.
     */
    public function deleted(Currency $currency): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Currency "restored" event.
     */
    public function restored(Currency $currency): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Currency "force deleted" event.
     */
    public function forceDeleted(Currency $currency): void
    {
        $this->clearLookupCache();
    }
}
