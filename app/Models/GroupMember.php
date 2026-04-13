<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $table = 'tblgroupmembers';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'GroupID',
        'ItemID',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'GroupID', 'ID');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemID', 'ID');
    }
}
