<?php

namespace App\Notifications\User\ExchangeMoney;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ExchangeMoney extends Notification
{
    use Queueable;

    public $user;
    public $data;
    public $trx_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$data,$trx_id)
    {
        $this->user = $user;
        $this->data = $data;
        $this->trx_id = $trx_id;
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
        $trx_id = $this->trx_id;
        $date = Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');

        return (new MailMessage)
            ->greeting("Hello ".$user->fullname." !")
            ->subject("Exchange Money From ". $data['requestData']['exchange_from_amount'].' '.$data['requestData']['exchange_from_currency'].' To '.$data['requestData']['exchange_to_amount'].' '.$data['requestData']['exchange_to_currency'])
            ->line(__("Your Exchange money request is successful From")." ".$data['requestData']['exchange_from_amount'].' '.$data['requestData']['exchange_from_currency'].' To '.$data['requestData']['exchange_to_amount'].' '.$data['requestData']['exchange_to_currency'])
            ->line(__("web_trx_id")." : " .$trx_id)
            ->line(__("request Amount")." : " .get_amount($data['requestData']['exchange_from_amount'],$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit))
            ->line(__("Exchange Rate")." : " ." 1 ". $data['requestData']['exchange_from_currency'].' = '. get_amount($data['chargeCalculate']->exchange_rate,$data['requestData']['exchange_to_currency'],$data['chargeCalculate']->precision_digit))
            ->line(__("Fees & Charges")." : " . get_amount($data['chargeCalculate']->total_charge,$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit))
            ->line(__("Will Get")." : " .  get_amount($data['requestData']['exchange_to_amount'],$data['requestData']['exchange_to_currency'],$data['chargeCalculate']->precision_digit))
            ->line(__("Total Payable Amount")." : " . get_amount($data['chargeCalculate']->payable,$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit))
            ->line(__("Status").": ". "success")
            ->line(__("Time & Date")." : " .$dateTime)
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
