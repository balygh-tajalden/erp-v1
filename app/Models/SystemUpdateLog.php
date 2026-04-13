<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUpdateLog extends Model
{
    protected $table = 'tblsystemupdatelogs';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'UpdateID',
        'ExecutionDate',
        'ExecutedBy',
        'ExecutionStatus',
        'ExecutionTime',
        'AffectedRows',
        'ErrorMessage',
        'AppliedChanges',
        'Warnings',
    ];

    protected $casts = [
        'ExecutionDate' => 'datetime',
    ];

    public function systemUpdate()
    {
        return $this->belongsTo(SystemUpdate::class, 'UpdateID', 'ID');
    }
}
