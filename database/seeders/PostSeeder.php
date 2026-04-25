<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Commentaire;
use App\Models\Reaction;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Exemple : Post par Alice
        $post1 = Post::create([
            'titre' => 'Bienvenue sur le forum',
            'contenu' => 'Ceci est le premier post du forum !',
            'user_id' => 3, // Alice
            'categorie_id' => 1,
        ]);

        // Commentaire sur le post
        Commentaire::create([
            'contenu' => 'Super post !',
            'user_id' => 4, // Bob
            'post_id' => $post1->id,
        ]);

        // Réaction Like
        Reaction::create([
            'type' => 'LIKE',
            'user_id' => 4,
            'post_id' => $post1->id,
        ]);
    }
}