<?php
require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$mail->limit = 1;

$items = $mail->getInboxMessages();

if($items->status === 1 && $items->messages){
    foreach($items->messages as $item){
        $mail->deleteMessage($item->message_id);
    }
}