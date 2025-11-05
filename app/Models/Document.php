<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id', 'uploaded_by', 'filename', 'path', 'mime', 'size', 'version'
    ];

    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
