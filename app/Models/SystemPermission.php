<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemPermission extends Model
{
    protected $table = 'tblsystempermissions';
    protected $primaryKey = 'ID';
    public $timestamps = false; // No need for timestamps for static dictionaries

    protected $fillable = [
        'PermissionCode',
        'ArabicName',
        'Category',
        'IsActive',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];
}
