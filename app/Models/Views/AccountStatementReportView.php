<?php

namespace App\Models\Views;

use App\Models\Entry;
use App\Models\Account;
use App\Models\Currency;
use App\Models\ReadOnlyModel;

class AccountStatementReportView extends ReadOnlyModel
{
    protected $table = 'vw_accountstatementreport';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $casts = [
        'id' => 'integer',
        'EntryID' => 'integer',
        'AccountID' => 'integer',
        'CurrencyID' => 'integer',
        'مدين' => 'decimal:4',
        'دائن' => 'decimal:4',
        'المبلغ' => 'decimal:4',
        'التاريخ' => 'date',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }
    public function entry()
    {
        return $this->belongsTo(Entry::class, 'EntryID', 'ID');
    }
}
