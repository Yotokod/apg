<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('subject')->nullable();
            $t->text('message');
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });

        Schema::table('orders', fn (Blueprint $t) => $t->index('user_id'));
        Schema::table('order_items', function (Blueprint $t) {
            $t->index('order_id');
            $t->index('product_id');
        });
        Schema::table('products', fn (Blueprint $t) => $t->index(['is_active', 'category']));
    }
};
