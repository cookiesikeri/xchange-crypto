<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLitecoinWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('litecoin_wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->longText('response');
            $table->longText('xpub');
            $table->longText('mnemonic');
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
        Schema::dropIfExists('litecoin_wallets');
    }
}
