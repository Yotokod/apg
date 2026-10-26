<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $t) {
            $t->id();
            $t->json('title')->nullable();
            $t->json('alt')->nullable();
            $t->string('image');
            $t->string('category')->default('autre')->index();
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('wp_id')->nullable()->unique();
            $t->timestamps();
        });
    }
};
