<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Reaction extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'post_id'
    ];

    // 🔗 Relation avec User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 Relation avec Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}