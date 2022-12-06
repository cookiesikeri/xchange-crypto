<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_cards', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->integer('number');
            $table->integer('expiryMonth');
            $table->integer('expiryYear');
            $table->integer('cvv2');
            $table->integer('defaultPin');
            $table->string('status');
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
        Schema::dropIfExists('virtual_cards');
    }
}
