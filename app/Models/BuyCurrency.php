<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Views\BuyCurrencyView;
use App\Traits\HasAuditTrail;

class BuyCurrency extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblBuyCurrencies';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'RowVersion',
        'Thenumber',
        'TheDate',
        'AccountID',
        'FundAccountID',
        'CurrencyID',
        'ExchangeCurrencyID',
        'Amount',
        'Price',
        'ExchangeAmount',
        'PurchaseMethod',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
        'BranchID',
        'EntryID',
        'SessionID',
        'CommissionAmount',
        'CommissionCurrencyID',
        'IsDeleted',
        'IsReversed',
        'ReferenceNumber',
    ];

    protected $casts = [
        'TheDate' => 'date',
        'Amount' => 'decimal:4',
        'Price' => 'decimal:6',
        'ExchangeAmount' => 'decimal:4',
        'CommissionAmount' => 'decimal:4',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'IsDeleted' => 'boolean',
        'IsReversed' => 'boolean',
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

    public function commissionCurrency()
    {
        return $this->belongsTo(Currency::class, 'CommissionCurrencyID', 'ID');
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class, 'EntryID', 'ID');
    }

    public function branch()
    {
        // tblBuyCurrencies uses BranchID as integer, link to Branch model
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function viewDetails()
    {
        return $this->hasOne(BuyCurrencyView::class, 'ID', 'ID');
    }

    protected static function booted()
    {
        static::deleting(function ($buyCurrency) {
            $buyCurrency->IsDeleted = true;
            $buyCurrency->saveQuietly();

            if ($buyCurrency->entry) {
                $buyCurrency->entry->delete();
            }
        });

        static::restoring(function ($buyCurrency) {
            $buyCurrency->IsDeleted = false;
            $buyCurrency->saveQuietly();

            if ($buyCurrency->entry()->withTrashed()->exists()) {
                $buyCurrency->entry()->withTrashed()->first()->restore();
            }
        });
    }
}
