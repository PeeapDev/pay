<?php

namespace Modules\Investment\Entities;

use App\Http\Helpers\Common;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;

class Profit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'invest_id', 'amount', 'term', 'calculated_at'
    ];

    public function getProfitsList($withOptions = [], $constraints, $selectOptions)
    {
        return $this::with($withOptions)->where($constraints)->get($selectOptions);
    }

    /**
     * [It will process profit calculation of an investment for user and will insert data to profit and invest detail log table]
     * @param  [object] $investment  [investment for which data will be processed]
     * @param  [int] $user_id  [User id who invested]
     */

    public function processInvestmentProfit($investment, $user_id)
    {
        try {

            //get last profit entry for user and invest
            $lastProfitEntry = Profit::where(['invest_id' => $investment->id, 'user_id' => $user_id])->orderBy('id', 'desc')->first();

            //if last profit is not empty date will be last profit calculated time or investment start time
            $date = ($lastProfitEntry == null) ? $investment->start_time : $lastProfitEntry->calculated_at;

            //calculating term
            $term = ($lastProfitEntry == null) ? 0 : $lastProfitEntry->term;
            $nextTerm = 0;
            $receiveAmount = $investment->received_amount;

            //existing profit count check for invest and user
            $existingProfitCount = Profit::where(['invest_id' => $investment->id, 'user_id' => $user_id])->count();

            $existingProfitCount = $existingProfitCount == 0 ? 1 : $existingProfitCount + 1;

            for ($i = $existingProfitCount; $i <= $investment->term_total; $i++) {
                // if profit count is equal or greater than investment term total exit from this process 

                if ($i > $investment->term_total) {
                    break;
                }
                //getting the investment plan
                $plan = InvestmentPlan::where('id', $investment->investment_plan_id)->first(['interest_time_frame', 'capital_return_term']);

                // calculating profit interval from plan interest frame
                $interval = profitInterval($plan->interest_time_frame);

                //calculating time period
                $period = date("Y-m-d H:i:s", strtotime('+' . $interval . 'hours', strtotime($date)));

                $date = $period;

                $currentDateTime = date("Y-m-d H:i:s");
                //if period is greater than current date and time break this process
                if ($period > $currentDateTime) break;

                // //calculating next term

                $nextTerm = $term + 1;
                $term = $nextTerm;

                //profit amount calculation
                $profitAmount = ($plan->capital_return_term == 'Term Basis')
                    ? ($investment->total / $investment->term_total)
                    : ($investment->estimate_profit / $investment->term_total);

                $profitData[] = [
                    'user_id' => $user_id,
                    'invest_id' => $investment->id,
                    'amount' => $profitAmount,
                    'term' => $nextTerm,
                    'calculated_at' => $period,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ] ;

                $investmentLogData [] = [
                    'user_id' => $user_id,
                    'invest_id' => $investment->id,
                    'type' => 'Profit',
                    'amount' => $profitAmount,
                    'description' => 'Profit',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $investmentData['received_amount'] = $receiveAmount + $profitAmount;
                $investmentData['term_count'] = $nextTerm;

                $receiveAmount = $receiveAmount + $profitAmount;
            }
            
            if (isset($profitData) && !empty($profitData)) {
                Profit::insert($profitData);
            } 
            if (isset($investmentLogData) && !empty($investmentLogData)) {
                InvestDetailLog::insert($investmentLogData);
            }
            if (isset($investmentData) && !empty($investmentData)) {
                $investment->update($investmentData);
            }

        } catch (Exception $e) {
            (new Common())->one_time_message('error', $e->getMessage());
        }
    }
}
