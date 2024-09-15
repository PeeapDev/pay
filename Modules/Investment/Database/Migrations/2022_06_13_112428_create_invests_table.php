<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invests', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('investment_plan_id')->unsigned()->index()->nullable();
            $table->foreign('investment_plan_id')->references('id')->on('investment_plans')->onUpdate('cascade')->onDelete('restrict');

            $table->integer('currency_id')->unsigned()->index()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('restrict');

            $table->integer('payment_method_id')->unsigned()->index()->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onUpdate('cascade')->onDelete('restrict');

            $table->string('uuid', 13)->unique()->comment("Unique ID (For Each Transfer)");
            $table->decimal('amount', 20, 8)->index('invests_amount_idx')->comment('100 USD');
            $table->decimal('estimate_profit', 20, 8)->comment('42 USD');
            $table->decimal('total', 20, 8)->index('invests_total_idx')->comment('142 USD');
            $table->decimal('received_amount', 20, 8)->nullable()->comment('142 USD; start from 0');
            $table->string('interest_rate', 15)->comment('7% or 7');
            $table->string('term', 30)->comment('7 Days');
            $table->integer('term_total')->comment('168');
            $table->integer('term_count')->comment('5');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->text('note')->nullable();
            $table->string('status', 15)->index()->default('Pending')->comment('Active or Pending or Cancelled or Completed');
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
        Schema::dropIfExists('invests');
    }
}
