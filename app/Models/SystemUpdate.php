<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    protected $table = 'tblsystemupdates';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'ScriptName',
        'ScriptContent',
        'Description',
        'Version',
        'IsExecuted',
        'ExecutedDate',
        'ExecutedBy',
        'ExecutionResult',
        'ScriptType',
        'ExecutionOrder',
        'Dependencies',
        'RollbackScript',
    ];

    protected $casts = [
        'IsExecuted' => 'boolean',
        'CreatedDate' => 'datetime',
        'ExecutedDate' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(SystemUpdateLog::class, 'UpdateID', 'ID');
    }
}
