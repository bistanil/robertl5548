<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings_logos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('active', 20);
            $table->string('type', 20);
            $table->string('language', 50);
            $table->string('title', 150);
            $table->text('slogan');
            $table->string('image', 150);
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
        Schema::drop('settings_logos');
    }
}
