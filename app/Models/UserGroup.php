<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class UserGroup extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblUserGroups';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'GroupName',
        'Description',
        'CreatedBy',
        'BranchID',
        'GroupNumber',
        'IsActive',
        'ModifiedBy',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'IsActive' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'UserGroupID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
