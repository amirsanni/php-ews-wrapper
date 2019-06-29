<?php

require "../vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$ews->tasks->limit = 10;

//Method takes an optional 'pageNumber' of type int
$res = $ews->tasks->get();

print_r($res);