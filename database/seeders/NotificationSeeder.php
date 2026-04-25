<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{

    public function run()
    {
       Notification::create([
    'message' => 'Bienvenue Alice !',
    'user_id' => 3
]);
    }
}
