<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operation extends Model
{
    use SoftDeletes;

    protected $table = 'tbloperations';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'OperationName',
    ];
}
