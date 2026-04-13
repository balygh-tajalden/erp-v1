<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'tblitems';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'ItemName',
        'ItemType',
        'IsGold',
        'IsCurrency',
        'Unit',
        'DefaultPrice',
        'Notes',
        'IsActive',
    ];

    protected $casts = [
        'IsGold' => 'boolean',
        'IsCurrency' => 'boolean',
        'DefaultPrice' => 'decimal:2',
        'IsActive' => 'boolean',
    ];

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'ItemID', 'ID');
    }

    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }
}
