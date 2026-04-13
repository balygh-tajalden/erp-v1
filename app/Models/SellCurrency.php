<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;
use App\Traits\HasSessionTracking;
use App\Models\Views\SellCurrencyView;

class SellCurrency extends Model
{
    use SoftDeletes, HasAuditTrail, HasSessionTracking;

    protected $table = 'tblSellCurrencies';
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
        'IsDeleted',
        'IsReversed',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
        'BranchID',
        'EntryID',
        'SessionID',
        'CommissionAmount',
        'CommissionCurrencyID',
        'ReferenceNumber',
        'Year',
    ];

    protected $casts = [
        'TheDate' => 'date',
        'Amount' => 'decimal:4',
        'Price' => 'decimal:6',
        'ExchangeAmount' => 'decimal:4',
        'CommissionAmount' => 'decimal:4',
        'IsDeleted' => 'boolean',
        'IsReversed' => 'boolean',
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
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function viewDetails()
    {
        return $this->hasOne(SellCurrencyView::class, 'ID', 'ID');
    }
    protected static function booted()
    {
        static::deleting(function ($sellCurrency) {
            // 1. تحديث حقل IsDeleted كإجراء شكلي للتقارير
            $sellCurrency->IsDeleted = true;
            $sellCurrency->saveQuietly(); // حفظ بدون استدعاء الأحداث مرة أخرى

            // 2. تحديث وحذف القيد المحاسبي (وهذا سيشغل الـ Trigger وينزل الرصيد)
            if ($sellCurrency->entry) {
                // تعديل حقل القيد إلى محذوف
                $sellCurrency->entry->isDeleted = 1;
                $sellCurrency->entry->saveQuietly();
                // حذفه فعلياً من الكود
                $sellCurrency->entry->delete();
            }
        });

        static::restoring(function ($sellCurrency) {
            // 1. الأستعادة
            $sellCurrency->IsDeleted = false;
            $sellCurrency->saveQuietly();

            // 2. استعادة القيد (والذي سيرفع الرصيد مرة أخرى)
            $entry = $sellCurrency->entry()->withTrashed()->first();
            if ($entry) {
                $entry->isDeleted = 0;
                $entry->saveQuietly();
                $entry->restore();
            }
        });
    }
}
