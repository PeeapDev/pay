<?php

namespace App\Lib;

use Textmagic\Services\TextmagicRestClient;
use Twilio\Rest\Client;

class SendSms
{
	public function nexmo($to,$fromName = 'admin',$message,$credentials){

		$basic  = new \Vonage\Client\Credentials\Basic($credentials->nexmo_api_key, $credentials->nexmo_api_secret);
		$client = new \Vonage\Client($basic);
		$response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS($to, $fromName, $message)
        );
		$message = $response->current();
	}
	public function twilio($to,$fromName,$message,$credentials){

		$account_sid = $credentials->account_sid;
		$auth_token = $credentials->auth_token;
		$twilio_number = $credentials->from;

		$client = new Client($account_sid, $auth_token);
		$client->messages->create(
		    '+'.$to,
		    array(
		        'from' => $twilio_number,
		        'body' => $message
		    )
		);
	}

	// public function textMagic($to,$fromName,$message,$credentials){
	// 	$client = new TextmagicRestClient($credentials->text_magic_username, $credentials->apiv2_key);
	//     $result = $client->messages->create(
	//         array(
	//             'text' => $message,
	//             'phones' => $to
	//         )
	//     );
	// }

}
