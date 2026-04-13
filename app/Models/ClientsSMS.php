<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientsSMS extends Model
{
    use HasFactory;

    protected $table = 'tblClientsSMS';
    protected $primaryKey = 'ClientID';

    protected $fillable = [
        'ClientName',
        'Username',
        'PasswordHash',
        'SMSBalance',
        'IsActive',
        'MaxDevices',
    ];

    public function deviceLicenses()
    {
        return $this->morphMany(DeviceLicense::class, 'licenseable');
    }

    public function smsRecharges()
    {
        return $this->hasMany(SMSRecharge::class, 'ClientID', 'ClientID');
    }

    protected $casts = [
        'ExpiryDate' => 'datetime',
        'IsActive' => 'boolean',
        'SMSBalance' => 'integer',
        'MaxDevices' => 'integer',
    ];
}
