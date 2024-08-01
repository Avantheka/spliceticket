<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('plan')->nullable();
            $table->string('slug')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('type');
            $table->date('plan_expire_date')->nullable();
            $table->integer('requested_plan')->default(0);
            $table->float('storage_limit')->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->string('avatar', 200)->nullable();
            $table->integer('parent')->default(0);
            $table->string('lang', 10)->default('en');
            $table->text('device_type')->nullable();
            $table->text('token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
