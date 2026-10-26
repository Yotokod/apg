<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $t) {
            $t->string('motion_file')->nullable(); // uploaded Lottie JSON (motion design explainer)
        });
    }
};
