<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatalogCategoriesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('catalog_id');
            $table->integer('parent');
            $table->integer('position');
            $table->string('active', 20);
            $table->string('language', 50);
            $table->string('title', 100)->unique();
            $table->string('slug', 100);
            $table->string('meta_title', 100);
            $table->string('meta_keywords', 200);
            $table->text('meta_description');            
            $table->text('content');
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
        Schema::drop('catalog_categories');
    }
}
