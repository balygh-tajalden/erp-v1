<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditTrail;
use App\Traits\HasSessionTracking;

class CurrencyPrice extends Model
{
    use HasSessionTracking;
    protected $table = 'tblCurrenciesPrices';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'SourceCurrencyID',
        'TargetCurrencyID',
        'ExchangePrice',
        'BuyPrice',
        'SellPrice',
        'CreatedBy',
        'BranchID',
        'CreatedDate',
        'MinBuyPrice',
        'MaxBuyPrice',
        'MinSellPrice',
        'MaxSellPrice',
        'Notes',
        'RowVersion',
        'PrevRowVersion',
        'SessionID',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
        'ExchangePrice' => 'decimal:6',
        'BuyPrice' => 'decimal:6',
        'SellPrice' => 'decimal:6',
        'MinBuyPrice' => 'decimal:6',
        'MaxBuyPrice' => 'decimal:6',
        'MinSellPrice' => 'decimal:6',
        'MaxSellPrice' => 'decimal:6',
    ];

    public function sourceCurrency()
    {
        return $this->belongsTo(Currency::class, 'SourceCurrencyID', 'ID');
    }

    public function targetCurrency()
    {
        return $this->belongsTo(Currency::class, 'TargetCurrencyID', 'ID');
    }
}
