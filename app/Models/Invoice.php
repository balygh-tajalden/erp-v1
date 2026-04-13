<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class Invoice extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblinvoices';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'InvoiceType',
        'AccountID',
        'PaymentAccountID',
        'InvoiceTypeID',
        'CustomerID',
        'EntryID',
        'TheNumber',
        'TheDate',
        'Notes',
        'CreatedBy',
        'BranchID',
        'SessionID',
    ];

    protected $casts = [
        'TheDate' => 'date',
        'CreatedDate' => 'datetime',
    ];

    public function type()
    {
        return $this->belongsTo(InvoiceType::class, 'InvoiceTypeID', 'ID');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(Account::class, 'PaymentAccountID', 'ID');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'ID');
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class, 'EntryID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'InvoiceID', 'ID');
    }

    public function getTotalAttribute()
    {
        return $this->details->sum('Total');
    }
}
