<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatalogAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalog_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('catalog_id');
            $table->integer('position');
            $table->string('active', 20);
            $table->string('language', 50);
            $table->string('title', 300);
            $table->string('is_list', 5);
            $table->string('is_filter', 5);
            $table->integer('list_id');
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
        Schema::drop('catalog_attributes');
    }
}
