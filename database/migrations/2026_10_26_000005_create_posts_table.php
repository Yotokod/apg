<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->json('title');
            $t->string('slug')->unique();
            $t->json('excerpt')->nullable();
            $t->json('body')->nullable();
            $t->string('cover')->nullable();
            $t->string('category')->nullable();
            $t->dateTime('published_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
};
