<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chat; // 🔥 AJOUT ICI

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        Chat::create([
            'message' => 'Salut Bob !',
            'user_id' => 3
        ]);
    }
}