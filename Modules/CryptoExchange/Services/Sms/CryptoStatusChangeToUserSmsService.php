<?php

/**
 * @package CryptoStatusChangeToUserSmsService
 * @author pay <dev@peeap.com>
 * @contributor mohamed <[dev@peeap.com]>
 * @created 03-06-2023
 */

namespace Modules\CryptoExchange\Services\Sms;

use Exception;
use Illuminate\Support\Str;
use App\Services\Sms\PeeapSms;

class CryptoStatusChangeToUserSmsService extends PeeapSms
{
    /**
     * The array of status and message whether sms sent or not.
     *
     * @var array
     */
    protected $smsResponse = [];

    public function __construct()
    {
        parent::__construct();
        $this->smsResponse = [
            'status'  => true,
            'message' => __('We have sent you the cryptoExchange status. Please check your sms.')
        ];
    }
    /**
     * Send forgot password code to cryptoExchange sms
     * @param object $cryptoExchange
     * @return array $response
     */
    public function send($cryptoExchange, $optional = [])
    {
        try {
            $response = $this->getSmsTemplate('crypto-transaction-status-update');

            if (!$response['status']) {
                return $response;
            }
                        
            $data = [
                "{user}" => getColumnValue($cryptoExchange->user),
                "{uuid}" => $cryptoExchange->uuid,
                "{transaction_type}" => $optional['type'],
                "{status}" => $cryptoExchange->status,
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            sendSMS($optional['phone'], $message);
        } catch (Exception $e) {
            $this->smsResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->smsResponse;
    }
}
