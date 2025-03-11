<?php

namespace App\Notifications\User\AddMoney;

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
        $date = Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');
        return (new MailMessage)
                    ->greeting(__("Hello")." ".$user->fullname." !")
                    ->subject(__("Add Money Via")." ".@$data->currency->name)
                    ->line(__("Admin Approved Your Add Money Request via")." ".@$data->currency->name." ,".__("details of add money").":")
                    ->line(__("request Amount").": " . get_amount($data->request_amount,$data->creator_wallet->currency->code))
                    ->line(__("Exchange Rate").": " ." 1 ". $data->creator_wallet->currency->code.' = '. get_amount($data->details->amount->exchange_rate??$data->currency->rate,$data->currency->currency_code))
                    ->line(__("Fees & Charges").": " .get_amount($data->charge->total_charge,$data->currency->currency_code))
                    ->line(__("Will Get").": " . get_amount(@$data->request_amount,$data->creator_wallet->currency->code))
                    ->line(__("Total Payable Amount").": " . get_amount(@$data->payable,$data->currency->currency_code))
                    ->line(__("web_trx_id").": " .@$data->trx_id)
                    ->line(__("Status").": ".__("Success"))
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
