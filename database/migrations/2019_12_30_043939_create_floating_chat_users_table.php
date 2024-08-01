<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFloatingChatUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'floating_chat_users', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->string('email');
            $table->tinyInteger('is_end')->default(0);
            $table->string('company_id');

            $table->timestamps();
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('floating_chat_users');
    }
}
