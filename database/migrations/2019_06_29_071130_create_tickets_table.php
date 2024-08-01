<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ticket_id',100)->unique();
            $table->string('name');
            $table->string('email');
            $table->integer('category');
            $table->integer('priority')->default(0);
            $table->string('subject');
            $table->string('status');
            $table->longText('description');
            $table->string('created_by');
            $table->longText('attachments');
            $table->dateTime('reslove_at');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
