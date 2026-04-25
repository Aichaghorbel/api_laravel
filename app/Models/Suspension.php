<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suspension extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'moderateur_id',
        'reason',
        'dateSusp'
    ];

    // 🔗 utilisateur suspendu
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 modérateur
    public function moderateur()
    {
        return $this->belongsTo(User::class, 'moderateur_id');
    }
}