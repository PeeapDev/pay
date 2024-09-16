<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeDirectionsTable extends Migration
{
    public function up()
    {
        Schema::create('exchange_directions', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('from_currency_id')->unsigned()->index('exchange_directions_from_currency_id_idx');
	        $table->foreign('from_currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('restrict');
			$table->integer('to_currency_id')->unsigned()->index('exchange_directions_to_currency_id_idx');
	        $table->foreign('to_currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('restrict');
			$table->string('type', 100);
	        $table->string('exchange_from', 30)->default('local');
			$table->decimal('exchange_rate', 20, 8)->nullable();
			$table->decimal('fees_percentage', 20, 8)->default(0);
			$table->decimal('fees_fixed', 20, 8)->default(0);
			$table->decimal('min_amount', 20, 8)->default(0);
			$table->decimal('max_amount', 20, 8)->default(0);
			$table->text('payment_instruction')->nullable();
			$table->text('gateways')->nullable();
	        $table->string('status', 11)->default('Active');
			$table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();

        });
    }

    public function down()
    {
        Schema::dropIfExists('exchange_directions');
    }
}
