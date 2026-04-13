<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class GroupType extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblGroupTypes';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'GroupTypeName',
        'CreatedBy',
        'CreatedDate',
        'ModifiedBy',
        'ModifiedDate',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];
}
