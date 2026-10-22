<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('customer'); // superadmin|admin|customer
            $t->string('phone')->nullable();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->string('group')->default('general');
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->json('name');
            $t->string('slug')->unique();
            $t->string('category')->default('poulet');
            $t->json('description')->nullable();
            $t->unsignedInteger('price');
            $t->string('unit')->default('piece');
            $t->string('image')->nullable();
            $t->unsignedInteger('stock')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('phone');
            $t->string('email')->nullable();
            $t->string('city')->nullable();
            $t->string('country')->nullable();
            $t->unsignedBigInteger('total');
            $t->string('currency')->default('XOF');
            $t->string('status')->default('pending'); // pending|paid|shipped|cancelled
            $t->string('payment_method')->nullable();
            $t->string('payment_ref')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->unsignedInteger('price');
            $t->unsignedInteger('qty');
        });

        Schema::create('courses', function (Blueprint $t) {
            $t->id();
            $t->json('title');
            $t->string('slug')->unique();
            $t->json('description')->nullable();
            $t->unsignedInteger('price')->default(0);
            $t->string('image')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('course_modules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->json('title');
            $t->unsignedInteger('position')->default(0);
        });

        Schema::create('lessons', function (Blueprint $t) {
            $t->id();
            $t->foreignId('course_module_id')->constrained()->cascadeOnDelete();
            $t->json('title');
            $t->json('content')->nullable();
            $t->string('video_url')->nullable();
            $t->string('motion_url')->nullable(); // optional motion-design explainer
            $t->unsignedInteger('position')->default(0);
        });

        Schema::create('quiz_nodes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $t->json('question');
            $t->json('explanation')->nullable();
            $t->boolean('is_start')->default(false);
        });

        Schema::create('quiz_options', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quiz_node_id')->constrained()->cascadeOnDelete();
            $t->json('label');
            $t->boolean('is_correct')->default(false);
            $t->foreignId('next_node_id')->nullable()->constrained('quiz_nodes')->nullOnDelete();
        });

        Schema::create('enrollments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->boolean('paid')->default(false);
            $t->timestamps();
            $t->unique(['user_id', 'course_id']);
        });

        Schema::create('lesson_progress', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('score')->default(0);
            $t->timestamp('completed_at')->nullable();
            $t->unique(['user_id', 'lesson_id']);
        });

        Schema::create('payment_gateways', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('type')->default('api'); // api|transfer
            $t->boolean('enabled')->default(false);
            $t->text('config')->nullable(); // encrypted JSON (keys)
            $t->json('instructions')->nullable();
            $t->unsignedInteger('position')->default(0);
        });

        Schema::create('plans', function (Blueprint $t) {
            $t->id();
            $t->json('name');
            $t->unsignedInteger('price');
            $t->string('period')->default('month');
            $t->json('features')->nullable();
            $t->boolean('is_active')->default(true);
        });

        Schema::create('sim_steps', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->json('title');
            $t->json('body')->nullable();
            $t->unsignedInteger('position')->default(0);
        });

        Schema::create('sim_choices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sim_step_id')->constrained()->cascadeOnDelete();
            $t->json('label');
            $t->integer('cash')->default(0);
            $t->integer('birds')->default(0);
            $t->integer('mortality')->default(0);
            $t->integer('revenue_pct')->default(0);
            $t->boolean('is_bad')->default(false);
            $t->json('tip')->nullable();
        });

        Schema::create('chat_messages', function (Blueprint $t) {
            $t->id();
            $t->string('session_id')->index();
            $t->string('role'); // user|bot|agent
            $t->text('content');
            $t->timestamps();
        });
    }
};
