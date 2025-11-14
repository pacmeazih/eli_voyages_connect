<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'nom',
        'description',
        'requis',
        'ordre',
    ];

    protected $casts = [
        'requis' => 'boolean',
        'ordre' => 'integer',
    ];

    /**
     * Get the package this document belongs to
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
