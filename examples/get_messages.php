<?php

require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('amir.sanni@mainone.net', 'Razafindrakoto10');

$mail->limit = 30;

$mail->getInboxMessages();//Messages in inbox
$mail->getSentItems();
$mail->getDraftItems(1);
$mail->getOutboxItems(1);
$mail->getFavourites(1);
$mail->getJunkItems();
$mail->getDeletedMessages();
$mail->getArchivedMessages();
$mail->getContacts();
$mail->getTasks();