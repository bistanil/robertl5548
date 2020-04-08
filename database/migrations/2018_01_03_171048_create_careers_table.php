<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCareersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('careers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('active', 20);
            $table->string('language', 50);
            $table->integer('position'); 
            $table->string('title', 200);
            $table->string('slug', 100);
            $table->string('city', 200)->nullable();
            $table->string('meta_title', 100)->nullable();
            $table->string('meta_keywords', 200)->nullable();
            $table->text('meta_description')->nullable(); 
            $table->text('content');
            $table->softDeletes();
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
        Schema::dropIfExists('careers');
    }
}
