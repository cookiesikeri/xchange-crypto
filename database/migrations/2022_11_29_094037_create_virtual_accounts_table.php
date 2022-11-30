<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('virtual_id');
            $table->string('currency');
            $table->string('active');
            $table->string('balance');
            $table->string('accountBalance');
            $table->string('availableBalance');
            $table->string('frozen');
            $table->string('accountingCurrency');
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
        Schema::dropIfExists('virtual_accounts');
    }
}
