<?php

/**
 * @package InvestmentMatureMailService
 * @author peeap <dev@peeap.com>
 * @contributor mohamed  <[dev@peeap.com]>
 * @created 07-09-2024
 */

namespace  Modules\Investment\Services\Email;

use App\Services\Mail\TechVillageMail;
use Exception;

class InvestmentMatureMailService extends TechVillageMail
{
    /**
     * The array of status and message whether email sent or not.
     *
     * @var array
     */
    protected $mailResponse, $processedInvestmentData = [];

    public function __construct()
    {
        parent::__construct();
        $this->mailResponse = [
            'status'  => true,
            'message' => __('Investment mature notification mail has been sent.')
        ];
    }
    /**
     * Send forgot password code to user email
     * @param object $user
     * @return array $response
     */
    public function send($investments)
    {
        $alias = \Illuminate\Support\Str::slug('Notify User On Investment Mature');

        try {
            $response = $this->getEmailTemplate($alias);

            if (!$response['status']) {
                return $response;
            }

            foreach ($investments as $key => $investment) {
                $data = [
                    "{user}" => $investment['user'],
                    "{uuid}" =>  $investment['uuid'],
                    "{invested}" =>  $investment['amount'],
                    "{profit}" => $investment['profit'],
                    "{transfer_to_wallet}" => $investment['matureAmount'],
                    "{created_at}" =>  $investment['investTime'],
                    "{investment_plan}" =>  $investment['plan'],
                    "{soft_name}" => settings('name')
                ];

                $message = str_replace(array_keys($data), $data, $response['template']->body);

                $this->processedInvestmentData[] = [
                    'email' => $investment['email'],
                    'subject' => $response['template']->subject,
                    'message' => $message
                ];
            }

            return $this->processedInvestmentData;

        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }

        return $this->mailResponse;
    }
}
