<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestmentPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investment_plans', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('currency_id')->unsigned()->index('investment_plans_currency_id_idx');
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('restrict');

            $table->string('name')->unique()->index('investment_plans_name_idx');
            $table->string('slug')->unique()->index('investment_plans_slug_idx');
            $table->text('description')->nullable()->default(NULL);
            $table->string('investment_type', 6)->index('investment_plans_invest_type_idx')->comment('type [Fixed or Range], if type is fixed only amount is counted; if range amount & max_amount both will be considered');
            $table->integer('term')->comment('integer number');
            $table->string('term_type')->comment('Days or Weeks or Months or Year');
            $table->decimal('amount', 20, 8)->comment('If invest type is set to range amount will count as minimum amount'); 
            $table->decimal('maximum_amount', 20, 8)->nullable()->default(NULL);
            $table->decimal('interest_rate', 20, 8);
            $table->string('interest_rate_type', 7)->index('investment_plans_interest_rate_type_idx')->comment('Percent or Fixed'); 
            $table->string('interest_time_frame')->index('investment_plans_interest_time_frame_idx')->comment('Hourly or Daily or Weekly or Monthly or Yearly');
            $table->string('capital_return_term', 15)->comment('Term Basis or After Matured');
            $table->string('withdraw_after_matured', 15)->comment('Yes or No');
            $table->string('payment_methods')->nullable()->comment('Any payment methods active for investment');
            $table->integer('maximum_investors')->comment('Any integer number, total number of investment users can invest against a plan');
            $table->integer('maximum_limit_for_investor')->comment('Any integer number, how much investment one user can do against a single plan');
            $table->string('is_featured', 3)->index('investment_plans_is_featured_idx')->default('No')->comment('Yes or No');
            $table->string('is_locked', 3)->index('investment_plans_is_locked_idx')->default('No')->comment('Yes or No');
            $table->string('status', 9)->index('investment_plans_status_idx')->comment('Active or Inactive or Draft');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('investment_plans');
    }
}
