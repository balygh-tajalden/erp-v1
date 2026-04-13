<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditTrail;

class SystemSetting extends Model
{
    use HasAuditTrail;
    protected $table = 'tblsystemsettings';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'SettingKey',
        'SettingValue',
        'Description',
        'TheUserID',
        'BranchID',
        'LevelID',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];
}
