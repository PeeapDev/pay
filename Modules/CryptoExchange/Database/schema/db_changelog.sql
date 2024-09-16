UPDATE `metas` SET `url` = 'crypto-exchange/track-transaction/{uuid}' WHERE `metas`.`url` = 'track-transaction/{uuid}';


INSERT INTO `email_templates` (`language_id`, `name`, `alias`, `subject`, `body`, `lang`, `type`, `status`, `group`, `created_at`, `updated_at`) VALUES 
    ('1', 'Notify Admin on Crypto Exchange', 'notify-admin-on-crypto-exchange', 'Crypto Exchange Notification', 'Hi <b>{admin}</b>,\r\n <br><br>Amount <b>{amount}</b> has been Crypto Exchange by <b>{user}</br>\r\n <br><br><b><u><i>Here’s a brief overview of the Deposit:</i></u></b>\r\n <br><br><b><u>Crypto Exchange at:</u></b> {created_at}\r\n <br><br><b><u>Crypto Exchange via:</u></b> {payment_method}\r\n <br><br><b><u>Transaction ID:</u></b> {uuid}\r\n <br><br><b><u>Currency:</u></b> {code}\r\n <br><br><b><u>Amount:</u></b> {amount}\r\n <br><br><b><u>Fee:</u></b> {fee}\r\n <br><br>If you have any questions, please feel free to reply to this email.\r\n <br><br>Regards,\r\n <br><b>{soft_name}</b>', 'en', 'email', 'Active', 'Crypto Exchange', '2023-07-15 10:53:27', NULL), 
    ('2', 'Notify Admin on Crypto Exchange', 'notify-admin-on-crypto-exchange', '', '', 'ar', 'email', 'Active', 'Crypto Exchange', '2023-07-15 10:53:27', NULL), 
    ('3', 'Notify Admin on Crypto Exchange', 'notify-admin-on-crypto-exchange', '', '', 'fr', 'email', 'Active', 'Crypto Exchange', '2023-07-15 10:53:27', NULL), 
    ('4', 'Notify Admin on Crypto Exchange', 'notify-admin-on-crypto-exchange', '', '', 'pt', 'email', 'Active', 'Crypto Exchange', '2023-07-15 10:53:27', NULL), 
    ('5', 'Notify Admin on Crypto Exchange', 'notify-admin-on-crypto-exchange', '', '', 'ru', 'email', 'Active', 'Crypto Exchange', '2023-07-15 10:53:27', NULL), 
    ('6', 'Notify Admin on Crypto Exchange', 'notify-admin-on-crypto-exchange', '', '', 'es', 'email', 'Active', 'Crypto Exchange', '2023-07-15 10:53:27', NULL), 
    ('7', 'Notify Admin on Crypto Exchange', 'notify-admin-on-crypto-exchange', '', '', 'tr', 'email', 'Active', 'Crypto Exchange', '2023-07-15 10:53:27', NULL), 
    ('8', 'Notify Admin on Crypto Exchange', 'notify-admin-on-crypto-exchange', '', '', 'ch', 'email', 'Active', 'Crypto Exchange', '2023-07-15 10:53:27', NULL);