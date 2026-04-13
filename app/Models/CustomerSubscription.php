<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditTrail;

class CustomerSubscription extends Model
{
    use HasAuditTrail;

    protected $table = 'tblcustomersubscriptions';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'CustomerID',
        'PackageID',
        'StartDate',
        'EndDate',
        'PriceAtSubscription',
        'IsActive',
        'CreatedBy',
    ];

    protected $casts = [
        'StartDate' => 'datetime',
        'EndDate' => 'datetime',
        'PriceAtSubscription' => 'decimal:2',
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'ID');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'PackageID', 'ID');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
