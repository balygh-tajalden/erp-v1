<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseDocumentation extends Model
{
    protected $table = 'tbldatabasedocumentation';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'TableName',
        'TableDescription',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
    ];
}
