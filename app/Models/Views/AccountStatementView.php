<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;
use App\Models\Entry;
use App\Models\Currency;

class AccountStatementView extends ReadOnlyModel
{
    protected $table = 'vw_accountstatement';

    /**
     * The primary key for the view.
     * We use RecordID since 'id' doesn't exist.
     */
    protected $primaryKey = 'RecordID';
    protected $casts = [
        'EntryID' => 'integer',
        'RecordID' => 'integer',
        'AccountID' => 'integer',
        'CurrencyID' => 'integer',
        'Amount' => 'decimal:4',
        'MCAmount' => 'decimal:4',
        'التاريخ' => 'date',
        'تاريخ القيد' => 'date',
        'مدين' => 'decimal:4',
        'دائن' => 'decimal:4',
        'المبلغ' => 'decimal:4',
        'المبلغ المكافئ' => 'decimal:4',
    ];

    /**
     * Relationship to the Currency model.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    /**
     * Relationship to the Entry model.
     */
    public function entry()
    {
        return $this->belongsTo(Entry::class, 'EntryID', 'ID');
    }
}
