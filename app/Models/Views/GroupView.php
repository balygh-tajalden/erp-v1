<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class GroupView extends ReadOnlyModel
{
    protected $table = 'vwgroups';

    protected $casts = [
        'ID' => 'integer',
        'فعالة' => 'boolean',
        'تاريخ الإدخال' => 'datetime',
        'ModifiedBy' => 'integer',
        'ModifiedDate' => 'datetime',
        'PurposeID' => 'integer',
    ];
}
