<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LockProcess extends Model
{
    protected $table = 'tblLockProsesses'; // Note: Specific typo from database
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'RowVersion',
        'PrevRowVersion',
        'TheNumber',
        'LockDate',
        'Notes',
        'UserID',
        'BranchID',
        'EnterTime',
        'SessionID',
    ];

    protected $casts = [
        'LockDate' => 'datetime',
        'EnterTime' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }
}
