<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('language', 50);
            $table->string('status', 50);
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('vin');
            $table->integer('car_id');
            $table->integer('model_id');
            $table->integer('type_id');
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
        Schema::drop('offer_requests');
    }
}
