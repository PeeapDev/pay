<?php

/**
 * @package InvestmentNotifyMailService
 * @author peeap <dev@peeap.com>
 * @contributor mohamed <[pay.peeap@gmail.com]>
 * @created 08-09-2024
 */

namespace  Modules\Investment\Services\Email;

use App\Services\Mail\TechVillageMail;
use Exception;

class InvestmentNotifyMailService extends TechVillageMail
{
    /**
     * The array of status and message whether email sent or not.
     *
     * @var array
     */
    protected $mailResponse = [];

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
    public function send($investment, $optional = [])
    {
        $recipient = getRecipientFromNotificationSetting($optional);

        $alias = \Illuminate\Support\Str::slug('Notify Admin On Investment');

        try {
            $response = $this->getEmailTemplate($alias);

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{uuid}" => $investment->uuid,
                "{soft_name}" => settings('name'),
                "{created_at}" => dateFormat($investment->created_at, $investment->user_id),
                "{user}" => getColumnValue($investment->user),
                "{amount}" => moneyFormat(optional($investment->currency)->symbol, formatNumber($investment->amount, $investment->currency_id)),
                "{code}" =>  getColumnValue($investment->currency, 'code'),
                "{investment_plan}" => getColumnValue($investment->investmentPlan, 'name')
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
        
            $this->email->sendEmail($recipient['email'], $response['template']->subject, $message);


        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }

        return $this->mailResponse;
    }
}
