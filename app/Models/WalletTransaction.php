<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAuditTrail;

class WalletTransaction extends Model
{
    use HasFactory, HasAuditTrail;

    protected $table = 'tblwallettransactions';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'WalletAddress',
        'TransactionHash',
        'LogIndex',
        'BlockNumber',
        'BlockTimestamp',
        'FromAddress',
        'ToAddress',
        'Amount',
        'TokenAddress',
        'TokenSymbol',
        'TokenName',
        'IsIngoing',
        'Chain',
        'AccountID',
        'CurrencyID',
        'BranchID',
        'EntryID',
        'CreatedBy',
        'SessionID',
        'IsPosted',
        'IsDeleted',
        'Notes',
        'CreatedDate',
        'ModifiedDate',
        'ModifiedBy',
    ];

    protected $casts = [
        'BlockTimestamp' => 'datetime',
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'IsIngoing' => 'boolean',
        'Amount' => 'decimal:18',
        'IsPosted' => 'boolean',
        'IsDeleted' => 'boolean',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'ID');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'ID');
    }
}
