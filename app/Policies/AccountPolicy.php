<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use App\Policies\Traits\ChecksFilamentPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization, ChecksFilamentPermissions;

    protected function getDocumentTypeID(): string
    {
        return '11'; // دليل الحسابات
    }
}
