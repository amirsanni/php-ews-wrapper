<?php
require "vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$items = $ews->folders->get();

if($items->status === 1 && $items->folders){
    foreach($items->folders as $folder){
        echo PHP_EOL."Folder ID: {$folder->id}".PHP_EOL."Folder Name: {$folder->name}".PHP_EOL;
    }
}