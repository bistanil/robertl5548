<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportMarginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_margins', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('currency_id');
            $table->integer('type_id');
            $table->string('active', 20);
            $table->float('margin');
            $table->float('min');
            $table->float('max');
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
        Schema::drop('transport_margins');
    }
}
