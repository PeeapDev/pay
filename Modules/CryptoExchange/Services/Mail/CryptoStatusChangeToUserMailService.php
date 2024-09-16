<?php

/**
 * @package CryptoStatusChangeToUserMailService
 * @author abdul <paypeeap.com>
 * @contributor abdul <[dev@peeap.com]>
 * @created 16-09-2024
 */

namespace Modules\CryptoExchange\Services\Mail;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class CryptoStatusChangeToUserMailService extends TechVillageMail
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
            'message' => __('We have sent you the crypto Exchange status. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to cryptoExchange email
     * @param object $cryptoExchange
     * @return array $response
     */
    public function send($cryptoExchange, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('notify-user-on-crypto-exchange');

            if (!$response['status']) {
                return $response;
            }
 
            $data = [
                "{user}" => getColumnValue($cryptoExchange->user),
                "{uuid}" => $cryptoExchange->uuid,
                "{transaction_type}" => $optional['type'],
                "{send_via}" =>  ucfirst($cryptoExchange->send_via),
                "{receive_via}" => ucfirst($cryptoExchange->receive_via),
                "{amount}" => moneyFormat(optional($cryptoExchange->fromCurrency)->symbol, formatNumber($cryptoExchange->amount, $cryptoExchange->from_currency)),
                "{get_amount}" => moneyFormat(optional($cryptoExchange->toCurrency)->symbol, formatNumber($cryptoExchange->get_amount, $cryptoExchange->to_currency)),
                "{from/to}" => $optional['fromTo'],
                "{fee}" => moneyFormat(optional($cryptoExchange->fromCurrency)->symbol, formatNumber($cryptoExchange->fee, $cryptoExchange->from_currency)),
                "{status}" => $cryptoExchange->status,
                "{created_at}" => dateFormat($cryptoExchange->created_at, $cryptoExchange->user_id),
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $subject = str_replace("{uuid}", $cryptoExchange->uuid, $response['template']->subject);


            $this->email->sendEmail($optional['email'], $subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}
