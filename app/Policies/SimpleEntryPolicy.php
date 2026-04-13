<?php

namespace App\Policies;

use App\Models\SimpleEntry;
use App\Models\User;
use App\Policies\Traits\ChecksFilamentPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class SimpleEntryPolicy
{
    use HandlesAuthorization, ChecksFilamentPermissions;

    protected function getDocumentTypeID(): string
    {
        return '4'; // سند قيد بسيط
    }
}
