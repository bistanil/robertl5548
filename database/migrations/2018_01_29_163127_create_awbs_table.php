<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAwbsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('awbs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->string('service_type'); 
            $table->string('bank'); 
            $table->string('bank_account'); 
            $table->string('envelopes')->nullable(); 
            $table->string('packages'); 
            $table->string('weight');
            $table->string('expedition_payment');
            $table->string('cash_on_delivery');
            $table->string('cash_on_delivery_payment_at');
            $table->string('declared_value')->nullable();
            $table->string('contact_person_sender');  
            $table->string('comments');
            $table->string('content');
            $table->string('recipient_name');
            $table->string('recipient_contact_person');
            $table->string('recipient_phone');
            $table->string('recipient_fax')->nullable();
            $table->string('recipient_email');
            $table->string('recipient_county');
            $table->string('recipient_city');
            $table->string('recipient_street');
            $table->string('recipient_street_no')->nullable();
            $table->string('recipient_postal_code')->nullable();
            $table->string('recipient_block')->nullable();
            $table->string('recipient_scale')->nullable();
            $table->string('recipient_floor')->nullable();
            $table->string('recipient_apartment')->nullable();
            $table->integer('dimension_id');
            $table->string('package_height');
            $table->string('package_width');
            $table->string('package_length');
            $table->string('restitution')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('options');
            $table->string('packing')->nullable();
            $table->string('personal_information')->nullable();
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
        Schema::dropIfExists('awbs');
    }
}
