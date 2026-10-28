<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Interactive-story simulator: admin-editable parameters + branching graph columns on sim_steps / sim_choices. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sim_params')) {
            Schema::create('sim_params', function (Blueprint $t) {
                $t->id();
                $t->string('key')->unique();
                $t->decimal('value', 14, 3);
                $t->string('unit', 32)->nullable();
                $t->string('status', 16)->default('hypothese'); // apg | generique | hypothese
                $t->string('group', 32)->default('costs');
                $t->json('label');
                $t->json('note')->nullable();
                $t->unsignedInteger('position')->default(0);
                $t->timestamps();
            });
        }

        Schema::table('sim_steps', function (Blueprint $t) {
            if (! Schema::hasColumn('sim_steps', 'chapter')) {
                $t->unsignedTinyInteger('chapter')->default(1);
                $t->string('scene', 32)->nullable();
                $t->string('kind', 32)->default('story');
                $t->string('icon', 64)->nullable();
                $t->string('next')->nullable();
            }
        });

        Schema::table('sim_choices', function (Blueprint $t) {
            if (! Schema::hasColumn('sim_choices', 'code')) {
                $t->string('code', 64)->nullable();
                $t->unsignedInteger('position')->default(0);
                $t->string('icon', 64)->nullable();
                $t->json('hint')->nullable();
                $t->string('next')->nullable();
                $t->json('effects')->nullable();
                $t->json('goals')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sim_params');
        Schema::table('sim_steps', fn (Blueprint $t) => $t->dropColumn(['chapter', 'scene', 'kind', 'icon', 'next']));
        Schema::table('sim_choices', fn (Blueprint $t) => $t->dropColumn(['code', 'position', 'icon', 'hint', 'next', 'effects', 'goals']));
    }
};
