<?php 

    if (!function_exists('isFeatured')) {

        /**
         * Get is_featured status by HTML Label
         *
         * @param string $status
         *
         * @return HTMLString
         */
        function isFeatured($status = null)
        {
            if (empty($status)) {
                return '';
            }
            $statuses = [
                'yes' => ['text' => __('Yes'), 'label' => 'other', 'color' => 'white', 'background_color' => 'royalblue'],
                'no' => ['text' => __('No'), 'label' => 'other', 'color' => 'black', 'background_color' => null]
            ];

            $status = strtolower($status);
            return '<span class="label label-' . $statuses[$status]['label'] . '" style = "background-color:' .  $statuses[$status]['background_color'] . '; color :' . $statuses[$status]['color'] . '";>' . $statuses[$status]['text'] . '</span>';
        }
    }

    if (!function_exists('generateOptions')) {
        /**
         * Return all options
         *
         * @param array $options
         * @param string $selected
         * @param bool $optionKey
         * @param string $extraAttr
         *
         * @return string
         */
        function generateOptions($options = [], $selected = '', $optionKey = false, $extraAttr = '')
        {
            if (empty($options)) {
                return '';
            }

            $data = [];
            foreach ($options as $key => $value) {
                $val = $optionKey ? $key : $value;
                $data[] = '<option ' . $extraAttr . ' value="' . $val . '" ' . ($selected == $val ? 'selected' : '') . '>' . __($value) . '</option>';
            }

            return implode(' ', $data);
        }
    }

    if (!function_exists('termCount')) {

        /*
         * Return specified term count
         *
         * @param string $termType
         * @param string $interestTimeFrame
         *
         * @return number
        */

        function termCount($termType, $interestTimeFrame = null)
        {
            $termCount['Year'] = [
                'Yearly' => 1,
                'Monthly' => 12,
                'Weekly' => 52,
                'Daily' => 365,
                'Hourly' => 8760
            ];
            $termCount['Month'] = [
                'Monthly' => 1,
                'Weekly' => 4,
                'Daily' => 30,
                'Hourly' => 720
            ];
            $termCount['Week'] = [
                'Weekly' => 1,
                'Daily' => 7,
                'Hourly' => 168,
            ];
            $termCount['Day'] = [
                'Daily' => 1,
                'Hourly' => 24,
            ];
            $termCount['Hour'] = [
                'Hourly' => 1,
            ];

            if (is_null($interestTimeFrame)) {
                return  $termCount[$termType];
            }
            return $termCount[$termType][$interestTimeFrame];
        }
    }

    if (!function_exists('profitInterval')) {

        /*
         * Return specified interval count
         *
         * @param string $interestTimeFrame
         *
         * @return number
        */

        function profitInterval($interestTimeFrame)
        {
            $interval = [
                'Hourly' => 1,
                'Daily' => 24,
                'Weekly' => 168,
                'Monthly' => 720,
                'Yearly' => 8760,
            ];

            return $interval[$interestTimeFrame];
        }
    }

    if (!function_exists('investmentInterestRateType')) {

        /*
         * Return investment interest rate type
         *
         * @param object $investment
         *
         * @return string
        */

        function investmentInterestRateType($investmentPlan)
        {
            $interestRateType = formatNumber(optional($investmentPlan)->interest_rate, $investmentPlan->currency_id);

            if (optional($investmentPlan)->interest_rate_type == 'Percent') {
                $interestRateType .= '%';
            } elseif (optional($investmentPlan)->interest_rate_type == 'APR') {
                $interestRateType .= '% APR';
            } elseif (optional($investmentPlan->currency)->code) {
                $interestRateType .= ' ' . optional($investmentPlan->currency)->code;
            }
            
            return $interestRateType;
        }
    }

    
    if (!function_exists('investmentTransactionUpdate')) {
    
        /*
         * Investment Transaction Update
         *
         * @param object $transaction
         * @param string $status
         *
         * @return void
        */

        function investmentTransactionUpdate($transaction, $status)
        {
            $investmentStatus = settings('invest_start_on_admin_approval') == 'Yes' ? 'Pending' : 'Active';
            $transactionStatus = ((int) $status === "Success") ? $investmentStatus : "Blocked";
            $transaction->payment_status = $transactionStatus; 
            $transaction->status = $investmentStatus; 
            $transaction->save();

            \Modules\Investment\Entities\Invest::where(['uuid' => $transaction->uuid])->update(['status' => $investmentStatus]);
        }
    }