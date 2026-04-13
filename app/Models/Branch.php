<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class Branch extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblBranches';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'BranchName',
        'AccountID',
        'CreatedBy',
        'CreatedDate',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'BranchID', 'ID');
    }

    public function userGroups()
    {
        return $this->hasMany(UserGroup::class, 'BranchID', 'ID');
    }

    public function documentTypes()
    {
        return $this->hasMany(DocumentType::class, 'BranchID', 'ID');
    }
}
