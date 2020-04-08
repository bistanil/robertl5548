<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('active', 50);
            $table->string('language', 50);
            $table->string('title', 250);
            $table->string('meta_title', 250);
            $table->string('meta_keywords', 250);
            $table->string('slug', 200);
            $table->integer('position');
            $table->text('meta_description');
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
        Schema::drop('news_posts');
    }
}
