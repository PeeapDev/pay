<?php

namespace Modules\Investment\Jobs;

use Modules\Investment\Emails\SendInvestmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Bus\Queueable;
use App\Models\EmailConfig;

class ProcessInvestmentEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $investmentInfo = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($investmentInfo)
    {
        $this->investmentInfo = $investmentInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        foreach ($this->investmentInfo as $key => $value) {

            $emailConfig = EmailConfig::where(['email_protocol' => 'smtp', 'status' => '1'])->first();

            if (isset($emailConfig->email_protocol) && $emailConfig->email_protocol == 'smtp') {
                Config::set([
                    'mail.driver'     => isset($emailConfig->email_protocol) ? $emailConfig->email_protocol : '',
                    'mail.host'       => isset($emailConfig->smtp_host) ? $emailConfig->smtp_host : '',
                    'mail.port'       => isset($emailConfig->smtp_port) ? $emailConfig->smtp_port : '',
                    'mail.from'       => ['address' => isset($emailConfig->from_address) ? $emailConfig->from_address : '', 'name' => isset($emailConfig->from_name) ? $emailConfig->from_name : ''],
                    'mail.encryption' => isset($emailConfig->email_encryption) ? $emailConfig->email_encryption : '',
                    'mail.username'   => isset($emailConfig->smtp_username) ? $emailConfig->smtp_username : '',
                    'mail.password'   => isset($emailConfig->smtp_password) ? $emailConfig->smtp_password : '',
                ]);
                Mail::to($value['email'])->send(new SendInvestmentNotification($value));
            } else {
                require 'vendor/autoload.php';
                $mail = new PHPMailer(true);

                $admin = \App\Models\Admin::whereStatus('Active')->first(['first_name', 'last_name', 'email']);
                if (!empty($admin)) {
                    $mail->From     = $admin->email;
                    $mail->FromName = $admin->first_name . ' ' . $admin->last_name;
                    $mail->AddAddress($value['email'], isset($admin) ? $mail->FromName : 'N/A');
                    $mail->Subject = $value['subject'];
                    $mail->Body    = $value['message'];
                    $mail->addAddress($value['email']);
                    $mail->WordWrap = 50;
                    $mail->IsHTML(true);
                    $mail->CharSet  = 'UTF-8';
                    $mail->Encoding = 'base64';

                    if (!empty($attachedFile)) {
                        $mail->AddAttachment(public_path('/' . $path . '/' . $attachedFile, 'base64'));
                    }
                    $mail->Send();
                }
            }
        }
    }
}
