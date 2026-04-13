<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Views\DoubleEntryDetailView;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;
use Illuminate\Support\Str;

class Entry extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblEntries';
    protected $documentTypeID = 5;
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'DocumentID',
        'RecordNumber',
        'RecordID',
        'TheDate',
        'Notes',
        'CreatedBy',
        'BranchID',
        'IsPosted',
        'isDeleted', // This is a separate boolean in migration
        'IsReversed',
        'ReversalOfID',
        'ModifiedBy',
        'IsClosingEntry',
    ];

    protected $casts = [
        'TheDate' => 'date',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'IsPosted' => 'boolean',
        'isDeleted' => 'boolean',
        'IsReversed' => 'boolean',
        'IsClosingEntry' => 'boolean',
    ];

    public function details()
    {
        return $this->hasMany(EntryDetail::class, 'ParentID', 'ID');
    }

    public function viewDetails()
    {
        return $this->hasMany(DoubleEntryDetailView::class, 'ParentID', 'ID');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'DocumentID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    protected static function booted()
    {
        static::deleting(function ($entry) {
            $entry->isDeleted = 1;
            $entry->saveQuietly();
            $entry->details()->delete();
        });

        static::restoring(function ($entry) {
            $entry->isDeleted = 0;
            $entry->saveQuietly();
            $entry->details()->withTrashed()->restore();
        });
    }

    public function reversalOf()
    {
        return $this->belongsTo(Entry::class, 'ReversalOfID', 'ID');
    }

    public function reversedBy()
    {
        return $this->hasOne(Entry::class, 'ReversalOfID', 'ID');
    }

    public function getTotalDebitsAttribute()
    {
        return $this->details()->where('Amount', '>', 0)->sum('Amount');
    }

    public function getTotalCreditsAttribute()
    {
        return abs($this->details()->where('Amount', '<', 0)->sum('Amount'));
    }

    public function isBalanced()
    {
        return round($this->details()->sum('Amount'), 4) == 0;
    }
}
