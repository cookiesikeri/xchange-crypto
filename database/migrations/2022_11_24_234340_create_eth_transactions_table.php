<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEthTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eth_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('to');
            $table->string('currency');
            $table->string('amount');
            $table->string('fromPrivateKey');
            $table->longText('response');
            $table->string('status')->default(0);
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
        Schema::dropIfExists('eth_transactions');
    }
}
