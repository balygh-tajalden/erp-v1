<?php

namespace App\Observers;

use App\Models\CurrencyPrice;
use Illuminate\Support\Facades\Cache;

class CurrencyPriceObserver
{
    protected function clearLookupCache(CurrencyPrice $price): void
    {
        // مسح كاش سعر الصرف الخاص بهذه العملة
        Cache::forget("exchange_rate_{$price->SourceCurrencyID}_{$price->TargetCurrencyID}");
    }

    public function created(CurrencyPrice $price): void
    {
        $this->clearLookupCache($price);
    }

    public function updated(CurrencyPrice $price): void
    {
        $this->clearLookupCache($price);
    }

    public function deleted(CurrencyPrice $price): void
    {
        $this->clearLookupCache($price);
    }
}
