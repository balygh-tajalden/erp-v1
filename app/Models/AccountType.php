<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountType extends Model
{
    use SoftDeletes;

    protected $table = 'tblAccountTypes';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'AccountType',
        'CreatedDate',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
    ];
}
