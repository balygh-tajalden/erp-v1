<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class HistoryView extends ReadOnlyModel
{
    protected $table = 'vw_historyview';

    protected $casts = [
        'id' => 'integer',
        'تاريخ_العملية' => 'datetime',
        'البيانات_السابقة' => 'array',
        'البيانات_الجديدة' => 'array',
    ];
}
