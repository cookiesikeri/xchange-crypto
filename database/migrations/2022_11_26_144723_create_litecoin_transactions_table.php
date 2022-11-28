<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLitecoinTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('litecoin_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('sender_address');
            $table->string('sender_private_key');
            $table->string('receiver_address');
            $table->string('value');
            $table->longText('response');
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
        Schema::dropIfExists('litecoin_transactions');
    }
}
