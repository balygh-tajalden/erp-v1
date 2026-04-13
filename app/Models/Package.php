<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class Package extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblPackages';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'PackageName',
        'TypeID',
        'BasePrice',
        'DurationDays',
        'IsActive',
        'CreatedBy',
    ];

    protected $casts = [
        'BasePrice' => 'decimal:2',
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime',
    ];

    public function type()
    {
        return $this->belongsTo(PackageType::class, 'TypeID', 'ID');
    }

    public function prices()
    {
        return $this->hasMany(PackagePrice::class, 'PackageID', 'ID');
    }

    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'PackageID', 'ID');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
