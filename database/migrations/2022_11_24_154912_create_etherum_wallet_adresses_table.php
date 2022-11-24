<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEtherumWalletAdressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etherum_wallet_adresses', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('pub_key');
            $table->string('address');
            $table->string('index');
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
        Schema::dropIfExists('etherum_wallet_adresses');
    }
}
