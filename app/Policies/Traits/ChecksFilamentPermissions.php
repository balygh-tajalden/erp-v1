<?php

namespace App\Policies\Traits;

use App\Models\User;

trait ChecksFilamentPermissions
{
    /**
     * Get the document type ID (FormCode) for this policy.
     */
    abstract protected function getDocumentTypeID(): string;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->getDocumentTypeID(), 'view');
    }

    public function view(User $user, $model): bool
    {
        return $user->hasPermission($this->getDocumentTypeID(), 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->getDocumentTypeID(), 'add');
    }

    public function update(User $user, $model): bool
    {
        return $user->hasPermission($this->getDocumentTypeID(), 'edit');
    }

    public function delete(User $user, $model): bool
    {
        return $user->hasPermission($this->getDocumentTypeID(), 'delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission($this->getDocumentTypeID(), 'delete');
    }

    public function restore(User $user, $model): bool
    {
        return $user->hasPermission($this->getDocumentTypeID(), 'delete');
    }

    public function forceDelete(User $user, $model): bool
    {
        return $user->hasPermission($this->getDocumentTypeID(), 'delete');
    }
}
