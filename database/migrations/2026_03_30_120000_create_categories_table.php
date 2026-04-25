<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('titre')->unique();
            $table->text('description')->nullable();

            // ✅ AJOUTS DYNAMIQUES
            $table->string('icon')->nullable();   // ex: sports_soccer
            $table->string('color')->nullable();  // ex: #10b981

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};