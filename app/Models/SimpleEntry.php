<?php

namespace App\Models;

use App\Services\WhatsAppNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;
use App\Traits\HasSessionTracking;
use Illuminate\Support\Facades\Log;

class SimpleEntry extends Model
{
    use SoftDeletes, HasAuditTrail, HasSessionTracking;

    protected $table = 'tblSimpleEntries';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'TheNumber',
        'FromAccountID',
        'ToAccountID',
        'TheDate',
        'Amount',
        'CurrencyID',
        'EntryID',
        'Notes',
        'CreatedBy',
        'BranchID',
        'ModifiedBy',
        'SessionID',
        'IsReversed',
        'isDeleted',
        'ReferenceNumber',
        'RowVersion',
    ];

    protected $casts = [
        'TheDate' => 'date',
        'Amount' => 'decimal:4',
        'IsReversed' => 'boolean',
        'isDeleted' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'TheNumber' => 'integer',
        'ReferenceNumber' => 'string',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'FromAccountID', 'ID');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'ToAccountID', 'ID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class, 'EntryID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'ID');
    }

    protected static function booted()
    {
        static::deleting(function ($simpleEntry) {
            // 1. Sync isDeleted column
            $simpleEntry->isDeleted = true;
            $simpleEntry->save();

            // 2. Cascade delete to Entry and Details
            if ($simpleEntry->entry) {
                $simpleEntry->entry->delete();
            }
        });

        static::restoring(function ($simpleEntry) {
            // 1. Sync isDeleted column
            $simpleEntry->isDeleted = false;
            $simpleEntry->save();

            // 2. Cascade restore to Entry and Details
            if ($simpleEntry->entry()->withTrashed()->exists()) {
                $simpleEntry->entry()->withTrashed()->first()->restore();
            }
        });
    }
}
