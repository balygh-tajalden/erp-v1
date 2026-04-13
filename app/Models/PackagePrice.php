<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditTrail;

class PackagePrice extends Model
{
    use HasAuditTrail;

    protected $table = 'tblPackagePrices';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'PackageID',
        'Price',
        'EffectiveDate',
        'CreatedBy',
    ];

    protected $casts = [
        'Price' => 'decimal:2',
        'EffectiveDate' => 'datetime',
        'CreatedDate' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'PackageID', 'ID');
    }
}
