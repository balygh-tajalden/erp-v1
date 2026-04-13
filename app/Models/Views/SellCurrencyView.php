<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class SellCurrencyView extends ReadOnlyModel
{
    protected $table = 'vwsellcurrencies';

    protected $casts = [
        'ID' => 'integer',
        'الرقم' => 'integer',
        'Amount' => 'decimal:4',
        'BranchID' => 'integer',
        'سعر البيع' => 'decimal:4',
        'المبلغ المستلم' => 'decimal:4',
        'التاريخ' => 'date',
        'تاريخ الإدخال' => 'datetime',
        'آخر تعديل' => 'datetime',
        'EntryID' => 'integer',
    ];
}
