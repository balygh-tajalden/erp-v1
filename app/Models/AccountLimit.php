<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditTrail;

class AccountLimit extends Model
{
    use HasAuditTrail;

    protected $table = 'tblAccountLimits';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'RowVersion',
        'GroupID',
        'AccountID',
        'BranchID',
        'Amount',
        'CurrencyID',
        'Notes',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
