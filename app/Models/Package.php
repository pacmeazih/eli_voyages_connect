<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id',
        'name',
        'description',
        'destination',
        'duration',
        'price',
        'services',
        'includes',
        'excludes',
        'max_travelers',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Get the required documents for this package
     */
    public function documents()
    {
        return $this->hasMany(PackageDocument::class)->orderBy('ordre');
    }

    /**
     * Get the dossiers using this package
     */
    public function dossiers()
    {
        return $this->hasMany(Dossier::class);
    }
}
