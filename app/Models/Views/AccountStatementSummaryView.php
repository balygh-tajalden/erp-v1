<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;
use App\Models\Account;
use App\Models\Entry;
use App\Models\Currency;

class AccountStatementSummaryView extends ReadOnlyModel
{
    protected $table = 'vw_accountstatementsummary';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $casts = [
        'id' => 'integer',
        'AccountID' => 'integer',
        'CurrencyID' => 'integer',
        'مدين' => 'decimal:4',
        'دائن' => 'decimal:4',
        'صافي المبلغ' => 'decimal:4',
        'المبلغ المكافئ' => 'decimal:4',
        'أول تاريخ' => 'date',
        'آخر تاريخ' => 'date',
    ];
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }
}
