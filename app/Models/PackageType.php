<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageType extends Model
{
    protected $table = 'tblPackageType';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'TypeName',
        'Notes',
    ];

    public function packages()
    {
        return $this->hasMany(Package::class, 'TypeID', 'ID');
    }
}
