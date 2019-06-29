<?php

require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$ews->mail->limit = 30;

$draft_items = $ews->mail->draft();

if($draft_items->status === 1 && $draft_items->messages){
    foreach($draft_items->messages as $item){
        $ews->mail->send($item->message_id, $item->change_key);
    }
}