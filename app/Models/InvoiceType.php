<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceType extends Model
{
    protected $table = 'tblInvoiceTypes';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'InvoiceTypeName',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'InvoiceTypeID', 'ID');
    }
}
