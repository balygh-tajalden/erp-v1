<?php

namespace App\Policies;

use App\Models\Currency;
use App\Models\User;
use App\Policies\Traits\ChecksFilamentPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyPolicy
{
    use HandlesAuthorization, ChecksFilamentPermissions;

    protected function getDocumentTypeID(): string
    {
        return '10'; // العملات
    }
}
