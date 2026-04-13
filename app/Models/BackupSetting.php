<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    protected $table = 'BackupSettings';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'BackupPath',
        'IsAutoBackupEnabled',
        'AutoBackupTime',
        'RetentionDays',
        'BackupInterval',
        'BackupIntervalMinutes',
        'ManualBackupPath',
    ];

    protected $casts = [
        'IsAutoBackupEnabled' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];
}
