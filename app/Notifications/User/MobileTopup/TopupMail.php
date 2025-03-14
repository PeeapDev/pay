<?php

namespace App\Notifications\User\MobileTopup;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class TopupMail extends Notification
{
    use Queueable;

    public $user;
    public $data;
    public $charges;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$data,$charges)
    {
        $this->user     = $user;
        $this->data     = $data;
        $this->charges  = $charges;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $user = $this->user;
        $data = $this->data;
        $charges = $this->charges;
        $trx_id = $this->data->trx_id;
        $date = Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');

        return (new MailMessage)
                    ->greeting(__("Hello")." ".$user->fullname." !")
                    ->subject(__("Mobile Top Up For")." ". $data->topup_type.' ('.$data->mobile_number.' )')
                    ->line(__("Sender Mobile top Up Email Heading")." ".$data->topup_type." ,".__("details of mobile top up").":")
                    ->line(__("web_trx_id").": " .$trx_id)
                    ->line(__("request Amount").": " . get_amount($data->request_amount,$charges['sender_currency'],$charges['precision_digit']))
                    ->line(__("Fees & Charges").": " . get_amount($data->charges,$charges['sender_currency'],$charges['precision_digit']))
                    ->line(__("Total Payable Amount").": " . get_amount($data->payable,$charges['sender_currency'],$charges['precision_digit']))
                   ->line(__("Status").": " .$data->status)
                    ->line(__("Date And Time").": " .$dateTime)
                    ->line(__('Thank you for using our application!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
