<?php

require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$mail->limit = 30;

$draft_items = $mail->getDraftItems();

if($draft_items->status === 1 && $draft_items->messages){
    foreach($draft_items->messages as $item){
        $mail->sendMessage($item->message_id, $item->change_key);
    }
}