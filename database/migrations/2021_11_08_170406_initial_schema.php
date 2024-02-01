<?php

use App\Models\User;
use App\Models\ProjectInvite;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitialSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('identity_provider')->default(User::IDENTITY_PROVIDER_LOCAL);
            $table->string('identity_provider_external_id')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('language')->nullable();
            $table->string('version')->nullable();
            $table->string('external_id')->nullable();
            $table->foreignId('created_by_id')->nullable();

            $table->index('external_id');
            $table->timestamps();
        });

        Schema::create('questionnaire_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('questionnaire_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default(User::ROLE_VIEWER);
            $table->timestamps();

            $table->unique(['questionnaire_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('questionnaire_user');
        Schema::drop('questionnaires');
        Schema::drop('users');
    }
}
