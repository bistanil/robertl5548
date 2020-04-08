<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaticmetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings_staticmetas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('page', 50);
            $table->string('language', 50);
            $table->string('meta_title', 250);
            $table->string('meta_keywords', 250);
            $table->string('meta_description', 500);
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
        Schema::drop('settings_staticmetas');
    }
}
