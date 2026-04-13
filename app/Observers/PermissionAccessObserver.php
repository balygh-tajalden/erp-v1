<?php

namespace App\Observers;

use App\Models\PermissionAccess;
use Illuminate\Support\Facades\Cache;

class PermissionAccessObserver
{
    protected function invalidatePermissions(): void
    {
        // تغيير رقم الإصدار للصلاحيات سيؤدي لتجاهل كل الكاش القديم فوراً
        Cache::forever('perm_version', time());
    }

    public function saved(PermissionAccess $permission): void
    {
        $this->invalidatePermissions();
    }

    public function deleted(PermissionAccess $permission): void
    {
        $this->invalidatePermissions();
    }
}
