<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestDetailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invest_detail_logs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('invest_id')->unsigned()->index()->nullable();
            $table->foreign('invest_id')->references('id')->on('invests')->onUpdate('cascade')->onDelete('cascade');

            $table->string('type', 11)->index('invest_detail_logs_type_idx')->comment('Invest or Profit or Transfer');
            $table->decimal('amount', 20, 8);
            $table->text('description')->comment('Invest on diamond plan, Profit Earned, Transfer to wallet');
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
        Schema::dropIfExists('invest_detail_logs');
    }
}
