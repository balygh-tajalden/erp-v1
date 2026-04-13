<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class CurrencyPriceView extends ReadOnlyModel
{
    protected $table = 'vw_currencyprices';

    protected $casts = [
        'ID' => 'integer',
        'SourceCurrencyID' => 'integer',
        'TargetCurrencyID' => 'integer',
        'سعر التحويل' => 'decimal:4',
        'سعر الشراء' => 'decimal:4',
        'أقل سعر شراء' => 'decimal:4',
        'أعلى سعر شراء' => 'decimal:4',
        'سعر البيع' => 'decimal:4',
        'أقل سعر بيع' => 'decimal:4',
        'أعلى سعر بيع' => 'decimal:4',
        'تاريخ الإدخال' => 'datetime',
    ];
}
