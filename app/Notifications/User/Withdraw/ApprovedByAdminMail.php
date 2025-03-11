<?php

namespace App\Notifications\User\Withdraw;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ApprovedByAdminMail extends Notification
{
    use Queueable;

    public $user;
    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$data)
    {
        $this->user = $user;
        $this->data = $data;

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
        $trx_id = $this->data->trx_id;
        $date = Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');
            return (new MailMessage)
            ->greeting(__("Hello")." ".$user->fullname." !")
            ->subject("Withdraw Money Via ". $data->gateway_name.' ('.$data->gateway_type.' )')
            ->line("Your withdraw money request approved successfully via ".$data->gateway_name." , details of withdraw money:")
            ->line(__("web_trx_id").": " .$trx_id)
            ->line(__("request Amount").": " . get_amount($data->amount,$data->charges->wallet_cur_code,4))
            ->line(__("Exchange Rate").": " ." 1 ". $data->charges->wallet_cur_code.' = '. get_amount($data->charges->exchange_rate,$data->charges->gateway_cur_code,4))
            ->line(__("Fees & Charges").": " . get_amount($data->charges->total_charge,$data->charges->wallet_cur_code,4))
            ->line(__("Will Get").": " .  get_amount($data->charges->will_get,$data->charges->gateway_cur_code,4))
            ->line(__("Total Payable Amount").": " . get_amount($data->charges->payable,$data->charges->wallet_cur_code,4))
            ->line("Status: ".__("Successful"))
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
