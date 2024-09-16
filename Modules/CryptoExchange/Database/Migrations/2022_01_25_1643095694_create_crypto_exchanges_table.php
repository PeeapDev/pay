<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoExchangesTable extends Migration
{
    public function up()
    {
        Schema::create('crypto_exchanges', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('crypto_exchagnes_user_id_idx')->nullable();
	        $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('from_currency')->unsigned()->index('crypto_exchagnes_from_currency_idx');
	        $table->foreign('from_currency')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('to_currency')->unsigned()->index('crypto_exchagnes_to_currency_idx');
	        $table->foreign('to_currency')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('restrict');
			$table->string('uuid', 13);
			$table->decimal('exchange_rate', 20, 8)->default(0);
			$table->decimal('amount', 20, 8)->default(0);
			$table->decimal('get_amount', 20, 8)->nullable();
			$table->decimal('fee', 20, 8)->default(0);
			$table->string('receiver_address', 100)->nullable();
			$table->string('receiving_details', 191)->nullable();
			$table->string('verification_via', 20)->nullable();
			$table->string('email_phone', 50)->nullable();
			$table->string('file_name', 191)->nullable();
			$table->string('payment_details', 191)->nullable();
			$table->string('send_via', 20)->nullable();
			$table->string('receive_via', 20)->nullable();
			$table->string('type', 20);
			$table->string('status', 20);
			$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('crypto_exchanges');
    }
}


