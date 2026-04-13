<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class ServiceProvider extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblServiceProviders';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'Name',
        'Prefixes',
        'Code',
        'Category',
        'IsActive',
        'DefaultProfit',
        'Logo',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'DefaultProfit' => 'decimal:2',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
    ];

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'ProviderID', 'ID');
    }
}
