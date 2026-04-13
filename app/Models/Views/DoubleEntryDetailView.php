<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class DoubleEntryDetailView extends ReadOnlyModel
{
    protected $table = 'vw_doubleentrydetails';
    public $timestamps = false;
    protected $primaryKey = 'ID';

    protected $casts = [
        'ID' => 'integer',
        'ParentID' => 'integer',
        'الرقم' => 'integer',
        'BranchID' => 'integer',
        'التاريخ' => 'date',
        'المبلغ دائن' => 'decimal:4',
        'المبلغ مدين' => 'decimal:4',
        'سعر التحويل' => 'decimal:4',
        'المقابل' => 'decimal:4',
        'وقت الإدخال' => 'datetime',
    ];
}
