<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class UserGroupView extends ReadOnlyModel
{
    protected $table = 'vw_usergroups';

    protected $casts = [
        'ID' => 'integer',
        'رقم المجموعة' => 'integer',
        'وقت الإدخال' => 'datetime',
    ];
}
