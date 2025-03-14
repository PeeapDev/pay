<?php

namespace App\Notifications\User\MobileTopup;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class Rejected extends Notification
{
    use Queueable;

    public $user;
    public $data;
    public $transaction;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$data,$transaction)
    {
        $this->user = $user;
        $this->data = $data;
        $this->transaction = $transaction;
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
        $transaction = $this->transaction;
        $details = $transaction->details;
        $trx_id = $this->data->trx_id;
        $date = Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');

        return (new MailMessage)
                    ->greeting(__("Hello")." ".$user->fullname." !")
                    ->subject(__("Mobile Top Up For")." ". $data->topup_type.' ('.$data->mobile_number.' )')
                    ->line(__("Admin rejected your mobile top up request")." ".$data->topup_type." ,".__("details of mobile top up").":")
                    ->line(__("web_trx_id").": " .$trx_id)
                    ->line(__("request Amount").": " . get_amount($details->charges->sender_amount,$details->charges->sender_currency))
                    ->line(__("Fees & Charges").": " . get_amount($details->charges->total_charge,$details->charges->sender_currency))
                    ->line(__("Will Get").": " . get_amount($details->charges->sender_amount,$details->charges->sender_currency))
                    ->line(__("Total Payable Amount").": " . get_amount($details->charges->payable,$details->charges->sender_currency))
                    ->line(__("Status").": " .$data->status)
                    ->line(__("Rejection Reason").": ". $data->reason)
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
