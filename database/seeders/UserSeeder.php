<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Administrateur
        User::create([
            'name' => 'Admin',
            'pseudo' => 'admin01',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'status' => 'actif',
        ]);

        // Modérateur
        User::create([
            'name' => 'Modérateur',
            'pseudo' => 'mod01',
            'email' => 'moderateur@example.com',
            'password' => Hash::make('123456'),
            'role' => 'moderateur',
            'status' => 'actif',
        ]);

        // Utilisateur normal
        User::create([
            'name' => 'Alice',
            'pseudo' => 'alice123',
            'email' => 'alice@example.com',
            'password' => Hash::make('123456'),
            'role' => 'utilisateur',
            'status' => 'actif',
        ]);

        User::create([
            'name' => 'Bob',
            'pseudo' => 'bob123',
            'email' => 'bob@example.com',
            'password' => Hash::make('123456'),
            'role' => 'utilisateur',
            'status' => 'actif',
        ]);
    }
}