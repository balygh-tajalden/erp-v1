<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class BuyCurrencyView extends ReadOnlyModel
{
    protected $table = 'vwbuycurrencies';

    protected $casts = [
        'ID' => 'integer',
        'الرقم' => 'integer',
        'Amount' => 'decimal:4',
        'BranchID' => 'integer',
        'سعر الشراء' => 'decimal:4',
        'المبلغ المدفوع' => 'decimal:4',
        'العمولة' => 'decimal:4',
        'التاريخ' => 'date',
        'تاريخ الإدخال' => 'datetime',
        'آخر تعديل' => 'datetime',
        'EntryID' => 'integer',
    ];
}
