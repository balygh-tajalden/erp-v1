<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'tblhistory';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'TableName',
        'RecordID',
        'ChangedBy',
        'ChangeDate',
        'OldData',
        'NewData',
        'Notes',
        'MachineName',
        'OSUserName',
        'OperationID',
        'FormName',
        'ActionType',
        'ActionDescription',
        'ActionDate',
        'UserID',
        'BranchID',
        'SessionID',
    ];

    protected $casts = [
        'ChangeDate' => 'datetime',
        'ActionDate' => 'datetime',
        'OldData' => 'array',
        'NewData' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function session()
    {
        return $this->belongsTo(AccountingSession::class, 'SessionID', 'legacy_id');
    }
}
