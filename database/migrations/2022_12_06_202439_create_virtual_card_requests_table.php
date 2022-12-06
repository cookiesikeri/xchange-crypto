<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualCardRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_card_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('brand');
            $table->string('currency');
            $table->string('issuerCountry');
            $table->string('status');
            $table->string('customerId');
            $table->string('allowedCategories');
            $table->string('blockedCategories');
            $table->string('atm');
            $table->string('pos');
            $table->string('web');
            $table->string('mobile');
            $table->string('interval');
            $table->string('amount');
            $table->string('sendPINSMS');
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
        Schema::dropIfExists('virtual_card_requests');
    }
}
