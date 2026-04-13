<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class CustomerReceipt extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblcustrecv';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'TheNumber',
        'AccountID',
        'FundAccountID',
        'TheDate',
        'Amount',
        'ExchangeAmount',
        'CurrencyID',
        'ExchangeCurrencyID',
        'EntryID',
        'Handling',
        'Notes',
        'CreatedBy',
        'BranchID',
        'ModifiedBy',
        'SessionID',
        'IsReversed',
        'isDeleted',
        'ReferenceNumber',
    ];

    protected $casts = [
        'TheDate' => 'date',
        'Amount' => 'decimal:4',
        'ExchangeAmount' => 'decimal:4',
        'IsReversed' => 'boolean',
        'isDeleted' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }

    public function fundAccount()
    {
        return $this->belongsTo(Account::class, 'FundAccountID', 'ID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    public function exchangeCurrency()
    {
        return $this->belongsTo(Currency::class, 'ExchangeCurrencyID', 'ID');
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class, 'EntryID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }
}
