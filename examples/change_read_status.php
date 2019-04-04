<?php
require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$mail->limit = 50;

$items = $mail->getInboxMessages();//$mail->getUnreadMessages()

if($items->status === 1 && $items->messages){
    foreach($items->messages as $item){
        $mail->markAsRead($item->message_id, $item->change_key);//$mail->markAsUnread($item->message_id, $item->change_key);
    }
}