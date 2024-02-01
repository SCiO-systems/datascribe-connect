<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBlocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('external_id')->nullable();
            $table->boolean('isGlobal')->nullable()->default(false);
            $table->foreignId('created_by_id')->nullable();

            $table->index('external_id');
            $table->timestamps();
        });

        Schema::create('block_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('block_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default(User::ROLE_VIEWER);
            $table->timestamps();

            $table->unique(['block_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('block_user');
        Schema::drop('blocks');
    }
}
