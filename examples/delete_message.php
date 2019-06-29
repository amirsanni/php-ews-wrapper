<?php
require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$ews->mail->limit = 3;

$items = $ews->mail->inbox();

if($items->status === 1 && $items->messages){
    foreach($items->messages as $item){
        $ews->mail->delete($item->message_id);
    }
}