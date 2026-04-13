<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $table = 'tblinvoicedetails';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'InvoiceID',
        'ItemID',
        'Quantity',
        'UnitPrice',
        'ManufactureCost',
    ];

    protected $casts = [
        'Quantity' => 'decimal:3',
        'UnitPrice' => 'decimal:2',
        'ManufactureCost' => 'decimal:2',
        'Total' => 'decimal:2', // Virtual column
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'InvoiceID', 'ID');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemID', 'ID');
    }
}
