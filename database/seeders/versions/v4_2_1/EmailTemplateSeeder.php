<?php

namespace Database\Seeders\versions\v4_2_1;

use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('email_templates')->insert(
            [
                [
                    'name' => 'Notice for Forgot Password',
                    'alias' => 'forgot-password-notice',
                    'subject' => 'Notice for Forgot Password',
                    'body' => 'Hi {user},<br><br>You recently requested a password reset for your account.<br><br>Please use the following code to reset your password<br><br><b><u>Code:</u></b> {password_reset_code} <br><br>If you did not make this request, please contact our support team immediately.<br><br>Regards,<br><b>{soft_name}</b>',
                    'language_id' => 1,
                    'lang' => 'en',
                    'type' => 'email',
                    'group' => 'General',
                    'status' => 'Active',
                ],
                [
                    'name' => 'Notice for Forgot Password',
                    'alias' => 'forgot-password-notice',
                    'subject'     => '',
                    'body'        => '',
                    'language_id' => 2,
                    'lang'        => 'ar',
                    'type'        => 'email',
                    'group' => 'General',
                    'status' => 'Active',
                    
                ],
                [
                    'name' => 'Notice for Forgot Password',
                    'alias' => 'forgot-password-notice',
                    'subject'     => '',
                    'body'        => '',
                    'language_id' => 2,
                    'lang'        => 'fr',
                    'type'        => 'email',
                    'group' => 'General',
                    'status' => 'Active',
                ],
                [
                    'name' => 'Notice for Forgot Password',
                    'alias' => 'forgot-password-notice',
                    'subject'     => '',
                    'body'        => '',
                    'language_id' => 4,
                    'lang'        => 'pt',
                    'type'        => 'email',
                    'group' => 'General',
                    'status' => 'Active',
                ],
                [
                    'name' => 'Notice for Forgot Password',
                    'alias' => 'forgot-password-notice',
                    'subject'     => '',
                    'body'        => '',
                    'language_id' => 5,
                    'lang'        => 'ru',
                    'type'        => 'email',
                    'group' => 'General',
                    'status' => 'Active',
                ],
                [
                    'name' => 'Notice for Forgot Password',
                    'alias' => 'forgot-password-notice',
                    'subject'     => '',
                    'body'        => '',
                    'language_id' => 6,
                    'lang'        => 'es',
                    'type'        => 'email',
                    'group' => 'General',
                    'status' => 'Active',
                ],
                [
                    'name' => 'Notice for Forgot Password',
                    'alias' => 'forgot-password-notice',
                    'subject'     => '',
                    'body'        => '',
                    'language_id' => 7,
                    'lang'        => 'tr',
                    'type'        => 'email',
                    'group' => 'General',
                    'status' => 'Active',
                ],
                [
                    'name' => 'Notice for Forgot Password',
                    'alias' => 'forgot-password-notice',
                    'subject'     => '',
                    'body'        => '',
                    'language_id' => 8,
                    'lang'        => 'ch',
                    'type'        => 'email',
                    'group' => 'General',
                    'status' => 'Active',
                ]
            ]

        );
    }
}





