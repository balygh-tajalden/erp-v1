<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class AccountView extends ReadOnlyModel
{
    protected $table = 'vw_accounts';

    protected $casts = [
        'ID' => 'integer',
        'وقت الإدخال' => 'datetime',
    ];
}
