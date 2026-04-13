<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionAccess extends Model
{
    protected $table = 'tblPermissionsAccess';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'FormCode',
        'TargetType',
        'TargetID',
        'PermissionValues',
        'DisplayText',
        'CreatedDate',
        'CreatedBy',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
        'PermissionValues' => 'json',
    ];
}
