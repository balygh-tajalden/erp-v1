<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemEvent extends Model
{
    protected $table = 'tblsystemevents';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'EventType',
        'DocumentName',
        'RecordID',
        'EventDescription',
        'EventDate',
        'UserID',
        'BranchID',
    ];

    protected $casts = [
        'EventDate' => 'datetime',
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
