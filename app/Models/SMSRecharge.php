<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SMSRecharge extends Model
{
    protected $table = 'tblSMSRecharges';
    protected $primaryKey = 'RechargeID';

    protected $fillable = [
        'ClientID',
        'MessagesCount',
        'Price',
        'Notes',
    ];

    public function client()
    {
        return $this->belongsTo(ClientsSMS::class, 'ClientID', 'ClientID');
    }
}
