<?php

require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$ews->mail->limit = 10;

//each of the methods takes an optional 'pageNumber' of type int
$ews->mail->inbox();//Messages in inbox
$ews->mail->unread(1);
$ews->mail->sent(3);
$ews->mail->draft();
$ews->mail->outbox(1);
$ews->mail->conversationHistory();
$ews->mail->favourites();//favorites() will also work
$ews->mail->junk();
$ews->mail->archived();
$ews->mail->deleted();