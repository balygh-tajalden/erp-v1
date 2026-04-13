<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemWindow extends Model
{
    protected $table = 'tblsystemwindows';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'WindowName',
        'WindowCode',
        'WindowDescription',
        'IsActive',
        'CreatedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'ID');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
