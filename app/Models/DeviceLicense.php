<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceLicense extends Model
{
    protected $table = 'tblDeviceLicenses';
    protected $primaryKey = 'LicenseID';

    protected $fillable = [
        'SystemName',
        'licenseable_type',
        'licenseable_id',
        'DeviceName',
        'DeviceKey',
        'Status',
    ];

    public function licenseable()
    {
        return $this->morphTo();
    }
}
