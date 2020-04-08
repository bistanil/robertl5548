<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatalogPriceProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('catalog_price_product', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('currency_id');
            $table->integer('product_id');
            $table->float('price');
            $table->float('old_price');
            $table->string('source', 100);
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
        //
        Schema::drop('catalog_price_product');
    }
}
