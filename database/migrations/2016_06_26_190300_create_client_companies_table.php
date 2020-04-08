<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->string('title', 300);
            $table->string('fiscal_code', 200);
            $table->string('registration_number', 200);
            $table->string('bank', 200);
            $table->string('bank_account');
            $table->string('address', 500);
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
        Schema::drop('client_companies');
    }
}
