<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    protected $table = 'tblAccountBalances';
    public $incrementing = false;
    protected $primaryKey = ['AccountID', 'CurrencyID', 'BranchID'];
    public $timestamps = false;

    // Note: Laravel doesn't support composite keys natively for some methods, 
    // but we define them here for clarity.

    protected $fillable = [
        'AccountID',
        'CurrencyID',
        'BranchID',
        'Balance',
        'LastUpdated',
    ];

    protected $casts = [
        'Balance' => 'decimal:2',
        'LastUpdated' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }
}
