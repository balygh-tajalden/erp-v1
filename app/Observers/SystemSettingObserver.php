<?php

namespace App\Observers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingObserver
{
    protected function clearLookupCache(): void
    {
        Cache::forget('system:settings');
    }

    public function saved(SystemSetting $setting): void
    {
        $this->clearLookupCache();
    }

    public function deleted(SystemSetting $setting): void
    {
        $this->clearLookupCache();
    }
}
