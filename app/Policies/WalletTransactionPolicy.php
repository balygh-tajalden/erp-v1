<?php

namespace App\Policies;

use App\Models\WalletTransaction;
use App\Models\User;
use App\Policies\Traits\ChecksFilamentPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletTransactionPolicy
{
    use HandlesAuthorization, ChecksFilamentPermissions;

    protected function getDocumentTypeID(): string
    {
        return '12'; // المحفظة
    }
}
