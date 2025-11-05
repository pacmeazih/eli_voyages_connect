<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Dossier extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'reference', 'title', 'notes'
    ];

    public static function booted()
    {
        static::creating(function ($dossier) {
            if (empty($dossier->reference)) {
                // generate ELI-YYYY-XXXXXX unique reference
                $year = date('Y');
                do {
                    $rand = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $ref = "ELI-{$year}-{$rand}";
                } while (DB::table('dossiers')->where('reference', $ref)->exists());

                $dossier->reference = $ref;
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
