<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('active', 20);
            $table->string('name', 200);
            $table->string('email', 200)->unique();
            $table->string('phone', 30);
            $table->string('password', 60);
            $table->string('gender', 10);
            $table->string('slug', 200);
            $table->string('origin', 200);
            $table->string('remember_token', 200);
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
        Schema::drop('clients');
    }
}
