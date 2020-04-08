<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWishlistItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wishlist_id');
            $table->integer('product_id');
            $table->integer('price_id');
            $table->string('title');
            $table->integer('qty');
            $table->float('unit_price');
            $table->float('subtotal');
            $table->string('currency');
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
        Schema::drop('wishlist_items');
    }
}
