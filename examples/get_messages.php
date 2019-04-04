<?php

require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('amir.sanni@mainone.net', 'Razafindrakoto10');

$mail->limit = 30;

//each of the methods takes an optional pageNumber of type int
$mail->getInboxMessages();//Messages in inbox
$mail->getSentItems(3);
$mail->getDraftItems();
$mail->getOutboxItems(1);
$mail->getConversationHistory();
$mail->getFavourites();
$mail->getJunkItems();
$mail->getDeletedMessages();
$mail->getArchivedMessages();
$mail->getContacts();
$mail->getTasks();