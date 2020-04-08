<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarrantiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('product_id');
            $table->string('product_title', 300);
            $table->string('qty', 300);
            $table->string('invoice_no', 20);
            $table->integer('client_id');
            $table->string('client_name', 300);
            $table->string('client_email', 300);
            $table->string('title', 300)->nullable();
            $table->date('start_date');
            $table->date('expiration_date');
            $table->string('docs');
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
        Schema::dropIfExists('warranties');
    }
}