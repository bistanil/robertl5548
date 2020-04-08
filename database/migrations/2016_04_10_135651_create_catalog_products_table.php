<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatalogProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('catalog_id');
            $table->integer('manufacturer_id');
            $table->string('active', 20);
            $table->string('language', 50);
            $table->string('code', 100)->unique();
            $table->string('title', 100);
            $table->string('slug', 100);
            $table->string('meta_title', 100);
            $table->string('meta_keywords', 200);
            $table->text('meta_description');            
            $table->text('content');
            $table->text('short_description');
            $table->string('first_page', 5);
            $table->string('offer', 5);
            $table->string('stock', 25);
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
        Schema::drop('catalog_products');
    }
}
