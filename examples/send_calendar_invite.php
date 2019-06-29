<?php
require "../vendor/autoload.php";

use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');

$ews->events->event_start = '2019-06-27 08:00:00';
$ews->events->event_end = '2019-06-27 10:00:00';
$ews->events->timezone = 'Africa/Lagos';//Any PHP Timezone
$ews->events->location = 'Fabac, VI, Lagos';
$ews->events->subject = 'Test';
$ews->events->event_body = 'This is a test event';
$ews->events->invitees = [
    ['name'=>'John Doe', 'email'=>'john.doe@example.com'],
    ['name'=>'Foo Bar', 'email'=>'foo.bar@example.com']
];

$res = $ews->events->create();

print_r($res);