<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVocabularies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vocabularies', function (Blueprint $table) {
            $table->id();
            $table->string('listname')->nullable();
            $table->string('description')->nullable();
            $table->string('external_id')->nullable();
            $table->boolean('isGlobal')->nullable()->default(false);
            $table->foreignId('created_by_id')->nullable();

            $table->index('external_id');
            $table->timestamps();
        });

        Schema::create('vocabulary_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vocabulary_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default(User::ROLE_VIEWER);
            $table->timestamps();

            $table->unique(['vocabulary_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vocabulary_user');
        Schema::drop('vocabularies');
    }
}
