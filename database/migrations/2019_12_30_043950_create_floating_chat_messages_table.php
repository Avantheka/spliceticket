<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFloatingChatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'floating_chat_messages', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->bigInteger('from');
            $table->bigInteger('to');
            $table->text('message');
            $table->tinyInteger('is_read');
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
        Schema::dropIfExists('floating_chat_messages');
    }
}
