<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class Customer extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblcustomers';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'CustomerName',
        'CustomerAddress',
        'Phone1',
        'Phone2',
        'Email',
        'AccountID',
        'IsActive',
        'CreatedBy',
        'BranchID',
        'Notes',
        'Latitude',
        'Longitude',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime',
        'Latitude' => 'decimal:8',
        'Longitude' => 'decimal:8',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }

    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'CustomerID', 'ID');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
