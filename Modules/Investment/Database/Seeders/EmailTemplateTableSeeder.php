<?php

namespace Modules\Investment\Database\Seeders;

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

        \App\Models\EmailTemplate::insert(
            [
                // Investment Email Notification to Admin
                [
                    'name' => 'Notify Admin On Investment',
                    'alias' => 'notify-admin-on-investment',
                    'subject' => 'Notice of Investment Transaction!',
                    'body' => 'Hi,
                                <br><br>Amount {amount} was invested by {user} on {investment_plan} plan.
                                <br><br><b><u><i>Here’s a brief overview of the Investment:</i></u></b>
                                <br><br><b><u>Created at:</u></b> {created_at}
                                <br><br><b><u>Transaction ID:</u></b> {uuid}
                                <br><br><b><u>Amount:</u></b> {amount}
                                <br><br>If you have any questions, please feel free to reply to this email.
                                <br><br>Regards,
                                <br><b>{soft_name}</b>
                                ',
                    'lang' => 'en',
                    'type' => 'email',
                    'language_id' => 1,
                    'group' => 'Investment',
                    'status' => 'Active',
                ],
                ['name' => 'Notify Admin On Investment', 'alias' => 'notify-admin-on-investment', 'subject'     => '', 'body' => '', 'lang' => 'ar', 'type' => 'email', 'language_id' => 2, 'group' => 'Investment',  'status' => 'Active'],
                ['name' => 'Notify Admin On Investment', 'alias' => 'notify-admin-on-investment', 'subject' => '', 'body' => '', 'lang' => 'fr', 'type' => 'email', 'language_id' => 3, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify Admin On Investment', 'alias' => 'notify-admin-on-investment', 'subject' => '', 'body' => '', 'lang' => 'pt', 'type' => 'email', 'language_id' => 4, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify Admin On Investment', 'alias' => 'notify-admin-on-investment', 'subject' => '', 'body' => '', 'lang' => 'ru', 'type' => 'email', 'language_id' => 5, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify Admin On Investment', 'alias' => 'notify-admin-on-investment', 'subject' => '', 'body' => '', 'lang' => 'es', 'type' => 'email', 'language_id' => 6, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify Admin On Investment', 'alias' => 'notify-admin-on-investment', 'subject' => '', 'body' => '', 'lang' => 'tr', 'type' => 'email', 'language_id' => 7, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify Admin On Investment', 'alias' => 'notify-admin-on-investment', 'subject' => '', 'body' => '', 'lang' => 'ch', 'type' => 'email', 'language_id' => 8, 'group' => 'Investment', 'status' => 'Active'],

                // Investment Status Updated Email Notification to User
                [
                    'name' => 'Investment Status Update',
                    'alias' => 'investment-status-update',
                    'subject' => 'Status of Investment has been updated!',
                    'body' => 'Hi {user},

                                <br><br><b>
                                Transaction of Investment #{uuid} has been updated to {status} by system administrator!</b>

                                <br><br>If you have any questions, please feel free to reply to this email.

                                <br><br>Regards,
                                <br><b>{soft_name}</b>',
                    'lang' => 'en',
                    'type' => 'email',
                    'language_id' => 1,
                    'group' => 'Investment', 
                    'status' => 'Active'
                ],
                ['name' => 'Investment Status Update', 'alias' => 'investment-status-update', 'subject' => '', 'body' => '', 'lang' => 'ar', 'type' => 'email', 'language_id' => 2, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Investment Status Update', 'alias' => 'investment-status-update', 'subject' => '', 'body' => '', 'lang' => 'fr', 'type' => 'email', 'language_id' => 3, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Investment Status Update', 'alias' => 'investment-status-update', 'subject' => '', 'body' => '', 'lang' => 'pt', 'type' => 'email', 'language_id' => 4, 'group' => 'Investment', 'status' => 'Active'],
                [ 'name' => 'Investment Status Update', 'alias' => 'investment-status-update', 'subject' => '', 'body' => '', 'lang' => 'ru', 'type' => 'email', 'language_id' => 5, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Investment Status Update', 'alias' => 'investment-status-update', 'subject' => '', 'body' => '', 'lang' => 'es', 'type' => 'email','language_id' => 6, 'group' => 'Investment', 'status' => 'Active'],
                [ 'name' => 'Investment Status Update', 'alias' => 'investment-status-update', 'subject' => '', 'body' => '', 'lang' => 'tr', 'type' => 'email', 'language_id' => 7, 'group' => 'Investment', 'status' => 'Active'],
                [ 'name' => 'Investment Status Update', 'alias' => 'investment-status-update', 'subject' => '', 'body' => '', 'lang' => 'ch', 'type' => 'email', 'language_id' => 8, 'group' => 'Investment', 'status' => 'Active'],

                // Investment Mature Email Notification to User
                [
                    'name' => 'Notify User On Investment Mature',
                    'alias' => 'notify-user-on-investment-mature',
                    'subject' => 'Notice of Investment Mature!',
                    'body' => 'Hi {user},
                                <br><br><b>
                                Your investment {uuid} on {investment_plan} plan mature recently.</b>
                                <br><br><b><u><i>Here’s a brief overview of the Investment:</i></u></b>
                                <br><br><b><u>Created at:</u></b> {created_at}
                                <br><br><b><u>Transaction ID:</u></b> {uuid}
                                <br><br><b><u>Invested :</u></b> {invested}
                                <br><br><b><u>Profit :</u></b> {profit}
                                <br><br><b><u>Transfer to wallet :</u></b> {transfer_to_wallet}
                                <br><br>If you have any questions, please feel free to reply to this email.
                                <br><br>Regards,
                                <br><b>{soft_name}</b>',
                    'lang' => 'en',
                    'type' => 'email',
                    'language_id' => 1,
                    'group' => 'Investment', 
                    'status' => 'Active'
                ],
                ['name' => 'Notify User On Investment Mature', 'alias' => 'notify-user-on-investment-mature', 'subject' => '', 'body' => '', 'lang' => 'ar', 'type' => 'email', 'language_id' => 2, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify User On Investment Mature', 'alias' => 'notify-user-on-investment-mature', 'subject' => '', 'body' => '', 'lang' => 'fr', 'type' => 'email', 'language_id' => 3, 'group' => 'Investment', 'status' => 'Active'
                ],
                ['name' => 'Notify User On Investment Mature', 'alias' => 'notify-user-on-investment-mature', 'subject' => '', 'body' => '', 'lang' => 'pt', 'type' => 'email', 'language_id' => 4, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify User On Investment Mature', 'alias' => 'notify-user-on-investment-mature', 'subject' => '', 'body' => '', 'lang' => 'ru', 'type' => 'email', 'language_id' => 5, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify User On Investment Mature', 'alias' => 'notify-user-on-investment-mature', 'subject' => '', 'body' => '', 'lang' => 'es', 'type' => 'email', 'language_id' => 6, 'group' => 'Investment', 'status' => 'Active'],
                [ 'name' => 'Notify User On Investment Mature', 'alias' => 'notify-user-on-investment-mature', 'subject' => '', 'body' => '', 'lang' => 'tr', 'type' => 'email', 'language_id' => 7, 'group' => 'Investment', 'status' => 'Active'],
                ['name' => 'Notify User On Investment Mature', 'alias' => 'notify-user-on-investment-mature', 'subject' => '', 'body' => '', 'lang' => 'ch', 'type' => 'email', 'language_id' => 8, 'group' => 'Investment', 'status' => 'Active'],
            ]
        );
    }
}
