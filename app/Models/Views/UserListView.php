<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class UserListView extends ReadOnlyModel
{
    protected $table = 'vw_userlist';

    protected $casts = [
        'ID' => 'integer',
        'تاريخ الإضافة' => 'datetime',
    ];
}
