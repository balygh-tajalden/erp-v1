<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateSetting extends Model
{
    protected $table = 'tblupdatesettings_file';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'UpdateServerUrl',
        'AutoUpdateEnabled',
        'CheckOnStartup',
        'CheckIntervalHours',
        'CurrentVersion',
        'LastCheckDate',
    ];

    protected $casts = [
        'AutoUpdateEnabled' => 'boolean',
        'CheckOnStartup' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'LastCheckDate' => 'datetime',
    ];
}
