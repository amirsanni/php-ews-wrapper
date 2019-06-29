<?php
require "../vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$ews->mail->sender_name = "Amir Sanni";
$ews->mail->subject = "Package Test";
$ews->mail->body = "Test email";
$ews->mail->recipient = 'amirsanni@gmail.com';//an array of email addresses is also acceptable
$ews->mail->recipient_name = "Amir Sanni";
$ews->mail->cc = ['john@doe.com', 'doe@john.com'];//single string is also acceptable
$ews->mail->bcc = 'foo@bar.com';//an array of email addresses is also acceptable
$ews->mail->attach = ['file1', 'file2', 'file3'];//single string is also acceptable  
$ews->mail->send_as_email = 'john@doe.com';
$ews->mail->reply_to = 'xyz@abc.com';

$ews->mail->send();