<?php

namespace App\Services\Currency;

use App\Services\BaseService;
use App\Models\CurrencyPrice;
use Illuminate\Support\Facades\Cache;

class ExchangeService extends BaseService
{
    /**
     * Replaces sp_GetExchangeRate
     */
    public function getRate($sourceId, $targetId)
    {
        $cacheKey = "exchange_rate_{$sourceId}_{$targetId}";

        return Cache::rememberForever($cacheKey, function () use ($sourceId, $targetId) {
            return CurrencyPrice::where('SourceCurrencyID', $sourceId)
                ->where('TargetCurrencyID', $targetId)
                ->latest('CreatedDate')
                ->value('ExchangePrice');
        });
    }
}
