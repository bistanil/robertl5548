<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('language', 50);
            $table->string('active', 50);
            $table->string('email1', 100);
            $table->string('email2', 100);
            $table->string('email3', 100);
            $table->string('phone1', 100);
            $table->string('phone2', 100);
            $table->string('phone3', 100);
            $table->string('map', 500);
            $table->text('address');
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
        Schema::drop('settings_contacts');
    }
}
