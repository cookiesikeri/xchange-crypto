<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftCardCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_card_customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('given_name');
            $table->string('family_name');
            $table->string('email_address');
            $table->string('address_line_1');
            $table->string('address_line_2');
            $table->string('locality');
            $table->string('administrative_district_level_1');
            $table->string('postal_code');
            $table->string('country');
            $table->string('phone_number');
            $table->string('reference_id');
            $table->longText('note');
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('gift_card_customers');
    }
}
