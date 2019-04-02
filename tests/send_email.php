<?php
require __DIR__."vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('email', 'password', 'optionalServerAddress');

$mail->sender_name = "Amir Sanni";
$mail->subject = "Package Test";
$mail->message = "Test email";
$mail->recipient = ['amirsanni@gmail.com'];
$mail->recipient_name = "Amir Sanni";
$mail->cc = ['john@doe.com', 'doe@john.com'];//single string is also acceptable
$mail->bcc = 'foo@bar.com';//an array of email addresses is also acceptable
$mail->files = ['file1', 'file2', 'file3'];//single string is also acceptable

echo $mail->send();