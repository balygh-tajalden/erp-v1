<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;
use App\Traits\HasSessionTracking;

class Currency extends Model
{
    use SoftDeletes, HasAuditTrail, HasSessionTracking;

    protected $table = 'tblCurrencies';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'TheNumber',
        'CurrencyName',
        'ArabicCode',
        'EnglishCode',
        'ISO_Code',
        'IsDefault',
        'CreatedBy',
        'SessionID',
        'BranchID',
        'ModifiedBy',
    ];

    protected $casts = [
        'CreatedDate' => 'datetime',
        'ModifiedDate' => 'datetime',
        'IsDefault' => 'boolean',
    ];

    public function pricesAsSource()
    {
        return $this->hasMany(CurrencyPrice::class, 'SourceCurrencyID', 'ID');
    }

    public function pricesAsTarget()
    {
        return $this->hasMany(CurrencyPrice::class, 'TargetCurrencyID', 'ID');
    }

    public function rateTo(Currency $target)
    {
        return $this->pricesAsSource()->where('TargetCurrencyID', $target->ID)->latest('CreatedDate')->first();
    }
}
