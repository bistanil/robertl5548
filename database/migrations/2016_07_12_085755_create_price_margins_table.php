<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceMarginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_margins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('active', 20);
            $table->integer('manufacturer_id');
            $table->integer('category_id');
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
        Schema::drop('price_margins');
    }
}
