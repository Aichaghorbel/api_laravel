<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['titre', 'contenu', 'image', 'user_id', 'categorie_id', 'is_locked', 'is_hidden'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }
}