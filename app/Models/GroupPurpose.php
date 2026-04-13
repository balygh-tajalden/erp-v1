<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class GroupPurpose extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblgrouptypespurposes';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'PurposeName',
        'GroupTypeID',
        'IsExclusive',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'IsExclusive' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];

    public function groupType()
    {
        return $this->belongsTo(GroupType::class, 'GroupTypeID', 'ID');
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'PurposeID', 'ID');
    }
}
