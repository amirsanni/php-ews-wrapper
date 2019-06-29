<?php
require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$ews->mail->limit = 10;

$items = $ews->mail->inbox();//$ews->mail->unread()

if($items->status === 1 && $items->messages){
    foreach($items->messages as $item){
        $ews->mail->markAsRead($item->message_id, $item->change_key);
        //$ews->mail->markAsUnread($item->message_id, $item->change_key);
    }
}