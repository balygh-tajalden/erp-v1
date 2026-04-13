<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class EntryDetail extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblEntryDetails';
    protected $primaryKey = 'ID';
    protected $touches = ['entry'];

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'ParentID',
        'Amount',
        'MCAmount',
        'CurrencyID',
        'AccountID',
        'Notes',
        'CostCenterID',
        'DetailedAccountID',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'Amount' => 'decimal:4',
        'MCAmount' => 'decimal:4',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];

    public function entry()
    {
        return $this->belongsTo(Entry::class, 'ParentID', 'ID');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    public function isDebit()
    {
        return $this->Amount > 0;
    }

    public function isCredit()
    {
        return $this->Amount < 0;
    }
}
