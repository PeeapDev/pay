<?php

/**
 * @package NotifyAdminOnCryptoMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace Modules\CryptoExchange\Services\Mail;

use Exception;
use App\Services\Mail\TechVillageMail;

class NotifyAdminOnCryptoMailService extends TechVillageMail
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
            'message' => __('We have sent you the Crypto details. Please check your email.')
        ];
    }
    /**
     * Send Notification to Administrator for Crypto Transaction
     * @param object $Crypto
     * @return array $response
     */
    public function send($crypto, $optional = [])
    {
        $recipient = getRecipientFromNotificationSetting($optional);

        try {
            $response = $this->getEmailTemplate('notify-admin-on-crypto-exchange');

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{created_at}" => dateFormat($crypto->created_at, $crypto->user_id),
                "{admin}" => $recipient['name'] ?? $recipient['email'],
                "{user}" => getColumnValue($crypto->user),
                "{uuid}" => $crypto->uuid,
                "{code}" => optional($crypto->fromCurrency)->code,
                "{payment_method}" => $crypto->send_via,
                "{from_wallet}" => optional($crypto->fromCurrency)->code,
                "{to_wallet}" => optional($crypto->toCurrency)->code,
                "{amount}" => moneyFormat(optional($crypto->fromCurrency)->symbol, formatNumber($crypto->amount, $crypto->from_currency)),
                "{fee}" => moneyFormat(optional($crypto->currency)->symbol, formatNumber($crypto->charge_fixed + $crypto->charge_percentage)),
                "{soft_name}" => settings('name'),
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);

            $this->email->sendEmail($recipient['email'], $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}
