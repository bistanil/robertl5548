<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->integer('company_id');
            $table->integer('transport_id');
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone');
            $table->string('client_gender');
            $table->string('client_company_title');
            $table->string('client_company_fiscal_code');
            $table->string('client_company_registration_number');
            $table->string('client_company_bank');
            $table->string('client_company_bank_account');
            $table->string('client_company_address');
            $table->string('client_delivery_address');
            $table->string('client_delivery_contact_person');
            $table->string('client_delivery_phone');
            $table->string('company_title');
            $table->string('company_vat_code');
            $table->string('company_registration_code');
            $table->string('company_address');
            $table->string('company_bank');
            $table->string('company_bank_account');
            $table->float('discount_amount');
            $table->float('transport_cost');
            $table->float('total');
            $table->string('currency');
            $table->string('language');
            $table->string('status');
            $table->integer('updated_by');
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
        Schema::drop('orders');
    }
}
