<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categorie;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        Categorie::create(['titre' => 'Général', 'description' => 'Discussion générale']);
        Categorie::create(['titre' => 'Tech', 'description' => 'Actualités tech']);
        Categorie::create(['titre' => 'Sport', 'description' => 'Discussions sportives']);
    }
}