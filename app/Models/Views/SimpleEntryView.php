<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class SimpleEntryView extends ReadOnlyModel
{
    protected $table = 'vw_simpleentries';

    protected $casts = [
        'ID' => 'integer',
        'الرقم' => 'integer',
        'BranchID' => 'integer',
        'المبلغ' => 'decimal:4',
        'التاريخ' => 'date',
        'FromAccountID' => 'integer',
        'ToAccountID' => 'integer',
        'CurrencyID' => 'integer',
        'تاريخ الإدخال' => 'datetime',
        'EntryID' => 'integer',
    ];
}
