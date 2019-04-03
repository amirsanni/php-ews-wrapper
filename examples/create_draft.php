<?php
require "../vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$mail->sender_name = "Amir Sanni";
$mail->subject = "Package Test";
$mail->message = "Test email";
$mail->recipient = 'amirsanni@gmail.com';//an array of email addresses is also acceptable
$mail->recipient_name = "Amir Sanni";
$mail->cc = ['john@doe.com', 'doe@john.com'];//single string is also acceptable
$mail->bcc = 'foo@bar.com';//an array of email addresses is also acceptable
$mail->attach = ['file1', 'file2', 'file3'];//single string is also acceptable  
$mail->send_as_email = 'john@doe.com';
$mail->reply_to = 'xyz@abc.com';

$mail->createDraft();