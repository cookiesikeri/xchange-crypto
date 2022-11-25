<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDodgecoinTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dodgecoin_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('txHash');
            $table->string('value');
            $table->string('address');
            $table->string('index');
            $table->string('signatureId');
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
        Schema::dropIfExists('dodgecoin_transactions');
    }
}
