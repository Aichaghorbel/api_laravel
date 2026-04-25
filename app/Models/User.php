<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // ✅ IMPORTANT : ajouter HasApiTokens ici
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'pseudo',
        'email',
        'password',
        'role',
        'status'
    ];

    // 🔹 Relations
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}