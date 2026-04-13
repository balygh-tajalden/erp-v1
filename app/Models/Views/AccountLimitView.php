<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class AccountLimitView extends ReadOnlyModel
{
    protected $table = 'vwaccountlimits';

    protected $casts = [
        'ID' => 'integer',
        'GroupID' => 'integer',
        'Amount' => 'decimal:4',
        'CurrencyID' => 'integer',
        'نشط' => 'boolean',
        'تاريخ الإنشاء' => 'datetime',
        'ModifiedBy' => 'integer',
        'تاريخ التعديل' => 'datetime',
        'BranchID' => 'integer',
    ];
}
