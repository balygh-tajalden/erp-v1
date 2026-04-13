<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class DocumentType extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblDocumentTypes';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'DocumentName',
        'TableName',
        'ViewName',
        'Notes',
        'IsActive',
        'IsHasFund',
        'IsHasEntry',
        'IsHasRestore',
        'CreatedBy',
        'BranchID',
        'CreatedDate',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'IsHasFund' => 'boolean',
        'IsHasEntry' => 'boolean',
        'IsHasRestore' => 'boolean',
        'CreatedDate' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
