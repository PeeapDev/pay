<?php

namespace Modules\CryptoExchange\Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        EmailTemplate::insert([

            // Exchange [en, ar, fr, pt, ru, es, tr, ch]
            // Notify to admin on money exchange
            [
                'name' => 'Notify Admin on Crypto Exchange',
                'alias' => 'notify-admin-on-crypto-exchange',
                'subject' => 'Crypto Exchange Notification',
                'body' => 'Hi <b>{admin}</b>,
                    <br><br>Amount <b>{amount}</b> has been Crypto Exchange by <b>{user}</br>
                    <br><br><b><u><i>Here’s a brief overview of the Deposit:</i></u></b>
                    <br><br><b><u>Crypto Exchange at:</u></b> {created_at}
                    <br><br><b><u>Crypto Exchange via:</u></b> {payment_method}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee:</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Crypto Exchange',
                'status' => 'Active',
            ],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            [
                'name' => 'Notify User on Crypto Exchange',
                'alias' => 'notify-user-on-crypto-exchange',
                'subject' => 'Crypto Exchange Notification',
                'body' => 'Hi <b>{user}</b>,
                    <br><br>We would like to inform you that the transaction for <b>{transaction_type}</b> with the ID #<b>{uuid}</b> has been updated to <b>{status}</b> by the system administrator.<br></div><b><br>
                    <br><br><b><u><i>Here’s a brief overview of the Transaction:</i></u></b>
                    <br><br><b><u>Exchange Amount:</u></b> {amount}
                    <br><br><b><u>Fee:</u></b> {fee}
                    <br><br><b><u>Get Amount:</u></b> {get_amount}
                    <br><br><b><u>Send via:</u></b> {send_via}
                    <br><br><b><u>Receive via:</u></b> {receive_via}
                    <br><br><b><u>Exchange at:</u></b> {created_at}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Crypto Exchange',
                'status' => 'Active',
            ],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'notify-user-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'notify-user-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'notify-user-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'notify-user-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'notify-user-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'notify-user-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'notify-user-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            [
                'name' => 'Notify User on Crypto Exchange',
                'alias' => 'crypto-transaction-status-update',
                'subject' => 'Crypto Exchange Status Update',
                'body' => 'Hi {user}, The transaction {transaction_type} with ID #{uuid} has been updated to {status}  by the administrator.Regards, {soft_name}.',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'sms',
                'group' => 'Crypto Exchange',
                'status' => 'Active',
            ],

            

            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'crypto-transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'sms', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'crypto-transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'sms', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'crypto-transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'sms', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'crypto-transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'sms', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'crypto-transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'sms', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'crypto-transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'sms', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify User on Crypto Exchange', 'alias' => 'crypto-transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'sms', 'group' => 'Crypto Exchange', 'status' => 'Active'],

        ]);
    }
}
