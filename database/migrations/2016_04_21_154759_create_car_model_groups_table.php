<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarModelGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_model_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('car_id');
            $table->integer('position');
            $table->string('active', 20);
            $table->string('language', 50);            
            $table->string('title', 100);            
            $table->string('image', 100)->nullable();
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
        Schema::drop('car_model_groups');
    }
}
