<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class ServiceRequest extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblServiceRequests';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'AccountID',
        'ProviderID',
        'ServiceType',
        'PhoneNumber',
        'Amount',
        'CurrencyID',
        'Cost',
        'Profit',
        'Status',
        'IsReversed',
        'TransactionID',
        'ResponseLog',
        'Notes',
        'ReferenceNumber',
        'EntryID',
        'BranchID',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'Amount' => 'decimal:4',
        'Cost' => 'decimal:4',
        'Profit' => 'decimal:4',
        'IsReversed' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'ServiceType' => 'integer',
    ];

    /**
     * العلاقات (Relationships)
     */

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'ProviderID', 'ID');
    }

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

    public function entry()
    {
        return $this->belongsTo(SimpleEntry::class, 'EntryID', 'ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'ID');
    }
}
