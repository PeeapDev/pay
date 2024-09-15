<?php

namespace Modules\Investment\Emails;

use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class SendInvestmentNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $receiverInfo = [];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($receiverInfo)
    {
        $this->receiverInfo = $receiverInfo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('investment::email.investment', ['receiverInfo' => $this->receiverInfo]);
    }
}
