<?php

/**
 * @package StatusChangeMailService
 * @author peeap <dev@peeap.com>
 * @contributor peeap <[pay.peeap@gmail.com]>
 * @created 09-09-2024
 */

namespace  Modules\Investment\Services\Email;

use App\Services\Mail\TechVillageMail;
use Exception;

class StatusChangeMailService extends TechVillageMail
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
            'message' => __('Investment status change notification mail has been sent.')
        ];
    }
    /**
     * Send forgot password code to user email
     * @param object $user
     * @return array $response
     */
    public function send($investment)
    {
        $alias = \Illuminate\Support\Str::slug('Investment Status Update');

        try {
            $response = $this->getEmailTemplate($alias);

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{user}" => getColumnValue($investment->user),
                "{uuid}" =>  $investment->uuid,
                "{status}" =>  $investment->status,
                "{soft_name}" => settings('name')
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
        
            $this->email->sendEmail(optional($investment->user)->email, $response['template']->subject, $message);

        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }

        return $this->mailResponse;
    }
}
