<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->string('number', 40)->unique();
            $t->string('code', 12)->unique();
            $t->string('student_name', 100);
            $t->json('course_title');
            $t->unsignedInteger('hours')->nullable();
            $t->unsignedTinyInteger('score')->nullable();
            $t->timestamp('issued_at');
            $t->timestamp('revoked_at')->nullable();
            $t->timestamp('renamed_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
