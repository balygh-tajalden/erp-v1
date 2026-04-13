<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WindowShortcut extends Model
{
    protected $table = 'tblwindowshortcuts';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'WindowName',
        'ShortcutKey',
        'ActionName',
        'Description',
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
