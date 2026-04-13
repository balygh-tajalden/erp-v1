<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountWhatsAppConfig extends Model
{
    protected $table = 'tblAccountWhatsAppConfig';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'ModifiedDate';

    protected $fillable = [
        'AccountID',
        'IsActive',
        'Settings',
    ];

    protected $casts = [
        'Settings' => 'array', // يحول الـ JSON تلقائياً إلى مصفوفة في Laravel
        'IsActive' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID', 'ID');
    }
}
