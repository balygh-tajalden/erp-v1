<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class CustomerReceiptView extends ReadOnlyModel
{
    protected $table = 'vw_customerreceipts';

    protected $casts = [
        'ID' => 'integer',
        'الرقم' => 'integer',
        'AccountID' => 'integer',
        'FundAccountID' => 'integer',
        'CurrencyID' => 'integer',
        'BranchID' => 'integer',
        'ExchangeAmount' => 'decimal:4',
        'المبلغ' => 'decimal:4',
        'مبلغ الحساب' => 'decimal:4',
        'ExchangeCurrencyID' => 'integer',
        'التاريخ' => 'date',
        'تاريخ الإدخال' => 'datetime',
        'EntryID' => 'integer',
    ];
}
