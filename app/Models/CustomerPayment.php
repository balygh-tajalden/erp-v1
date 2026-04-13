<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class CustomerPayment extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblcustpay';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'TheNumber',
        'AccountID',
        'FundAccountID',
        'TheDate',
        'Amount',
        'CurrencyID',
        'EntryID',
        'Handling',
        'CreatedBy',
        'ExchangeAmount',
        'ExchangeCurrencyID',
        'BranchID',
        'ModifiedBy',
        'IsReversed',
        'isDeleted',
        'ReferenceNumber',
        'Notes',
        'SessionID',
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
