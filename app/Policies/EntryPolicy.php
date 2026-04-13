<?php

namespace App\Policies;

use App\Models\Entry;
use App\Models\User;
use App\Policies\Traits\ChecksFilamentPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class EntryPolicy
{
    use HandlesAuthorization, ChecksFilamentPermissions;

    protected function getDocumentTypeID(): string
    {
        return '5'; // سند قيد مزدوج
    }
}
