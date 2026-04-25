<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'icon',     
        'color'    
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}