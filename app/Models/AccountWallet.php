<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class AccountWallet extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $table = 'tblAccountWallets';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';
    const DELETED_AT = 'DeletedDate';

    protected $fillable = [
        'AccountID',
        'WalletAddress',
        'Network',
        'Label',
        'IsActive',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'DeletedDate' => 'datetime',
    ];

    /**
     * الحساب المرتبط بهذا العنوان
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }
}
