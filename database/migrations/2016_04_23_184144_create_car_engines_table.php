<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarEnginesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_engines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_id');
            $table->integer('td_id');
            $table->integer('position');
            $table->string('active', 25);
            $table->string('language', 50);
            $table->string('code', 100);
            $table->integer('kw')->nullable();
            $table->integer('hp')->nullable();
            $table->integer('valves')->nullable();
            $table->integer('cylinders')->nullable();
            $table->integer('ccm')->nullable();
            $table->string('litres', 10)->nullable();
            $table->integer('crankshaft')->nullable();
            $table->integer('torque')->nullable();
            $table->string('extension', 10)->nullable();
            $table->string('drilling', 10)->nullable();
            $table->string('rpm', 10)->nullable();
            $table->string('design', 50)->nullable();
            $table->string('fuel', 50)->nullable();
            $table->string('fuel_supply', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('charge', 50)->nullable();
            $table->string('transmission', 50)->nullable();
            $table->string('cooling', 50)->nullable();
            $table->string('cylinders_description', 50)->nullable();
            $table->string('gas_norm', 50)->nullable();
            $table->string('search_code', 100);
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
        Schema::drop('car_engines');
    }
}
