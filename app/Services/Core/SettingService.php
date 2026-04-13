<?php

namespace App\Services\Core;

use App\Models\SystemSetting;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

/**
 * SettingService: Centralized management for tblSystemSettings.
 */
class SettingService extends BaseService
{
    /**
     * Get setting value by key with caching
     */
    public function get($key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            return SystemSetting::where('SettingKey', $key)->value('SettingValue') ?? $default;
        });
    }

    /**
     * Set or Update a setting
     */
    public function set($key, $value, $description = null)
    {
        $setting = SystemSetting::updateOrCreate(
            ['SettingKey' => $key],
            [
                'SettingValue' => $value,
                'Description'  => $description,
                'ModifiedBy'   => Auth::id(),
                'ModifiedDate' => now()
            ]
        );

        Cache::forget("setting_{$key}");
        
        return $setting;
    }
}
