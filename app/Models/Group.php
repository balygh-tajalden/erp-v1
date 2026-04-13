<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class Group extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblGroups';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'GroupName',
        'GroupTypeID',
        'PurposeID',
        'Notes',
        'CreatedBy',
        'IsActive',
        'ModifiedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];

    public function groupType()
    {
        return $this->belongsTo(GroupType::class, 'GroupTypeID', 'ID');
    }

    public function purpose()
    {
        return $this->belongsTo(GroupPurpose::class, 'PurposeID', 'ID');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class, 'GroupID', 'ID');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
