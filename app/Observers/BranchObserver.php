<?php

namespace App\Observers;

use App\Models\Branch;
use Illuminate\Support\Facades\Cache;
class BranchObserver
{
    protected function clearLookupCache(): void
    {
        // مسح القائمة العامة
        Cache::forget('lookup:branches');

    // مسح أي مفاتيح أخرى مرتبطة قد تضيفها مستقبلاً
    // Cache::forget('lookup:accounts_by_branch'); 
    }
    public function saved(Branch $branch): void
    {
        $this->clearLookupCache();

    }

    /**
     * Handle the Branch "created" event.
     */
    public function created(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "updated" event.
     */
    public function updated(Branch $branch): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Branch "deleted" event.
     */
    public function deleted(Branch $branch): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Branch "restored" event.
     */
    public function restored(Branch $branch): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the Branch "force deleted" event.
     */
    public function forceDeleted(Branch $branch): void
    {
        $this->clearLookupCache();
    }
}
