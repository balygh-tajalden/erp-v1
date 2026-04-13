<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroupMapping extends Model
{
    protected $table = 'tblUserGroupMapping';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null; // Composite key handled manually if needed

    protected $fillable = [
        'UserID',
        'GroupID',
        'AssignedBy',
        'AssignedDate',
    ];

    protected $casts = [
        'AssignedDate' => 'datetime',
    ];
}
