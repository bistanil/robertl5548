<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarModelTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_model_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('td_id');
            $table->integer('model_id');
            $table->integer('position');
            $table->string('active', 20);
            $table->string('language', 50);
            $table->string('title');
            $table->integer('construction_start_year');
            $table->integer('construction_start_month');
            $table->integer('construction_end_year');
            $table->integer('construction_end_month');
            $table->string('kw', 50)->nullable();
            $table->string('hp', 50)->nullable();
            $table->string('cc', 50)->nullable();
            $table->string('engine', 150)->nullable();
            $table->string('engine_code', 150)->nullable();
            $table->string('cylinders', 50)->nullable();
            $table->string('fuel', 100)->nullable();
            $table->string('body', 150)->nullable();
            $table->string('axle', 100)->nullable();
            $table->string('max_weight', 100)->nullable();
            $table->string('slug', 100)->nullable();
            $table->string('meta_title', 100)->nullable();
            $table->string('meta_keywords', 200)->nullable();
            $table->text('meta_description')->nullable();            
            $table->text('content');
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
        Schema::drop('car_model_types');
    }
}
