<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class CurrencyView extends ReadOnlyModel
{
    protected $table = 'vw_currencies';

    protected $casts = [
        'الرقم' => 'integer',
        'افتراضية' => 'boolean',
        'تاريخ الإضافة' => 'datetime',
        'تاريخ التعديل' => 'datetime',
        'الفرع' => 'integer',
    ];
}
