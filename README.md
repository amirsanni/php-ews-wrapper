# php-ews-wrapper
This is a library for communicating with Exchange Web Service. It provides a very simple and easy to use API,
leveraging on jamesiarmes/php-ews library. 


# Installation
```
composer require amirsanni/php-ews-wrapper
```


# Features
* Send Email
* Create Draft
* Send Messages in Draft
* Get Inbox Messages
* Get Unread Messages
* Change Message Read Status
* Delete Message
* Get Sent Items
* Get Outbox Items
* Get Draft Items
* Get Contacts
* Get Deleted Messages
* Get Archived Messages
* Get Messages in Favorites Folder
* Get Junk Messages
* Get Tasks
* Get Conversation History
* Get Folders List
* Create event


# How to use
### Instantiate

```
use amirsanni\phpewswrapper\PhpEwsWrapper;

$ews = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');
```

**Note:** Server address defaults to _outlook.office365.com_  
**Supported Versions: 2007, 2009, 2010, 2013, 2016**. _Defaults to 2016_.



## Send Email
```
$ews->mail->sender_name = "John Doe";
$ews->mail->subject = "Test email";
$ews->mail->body = "This is a test email";
$ews->mail->recipient = 'abc@example.com'; //['abc@xyz.com', 'abc@example.com']
$ews->mail->recipient_name = "Amir Sanni";
$ews->mail->cc = ['abc@xyz.com', 'abc@example.com']; //'abc@example.com'
$ews->mail->bcc = 'abc@example.com'; //['abc@xyz.com', 'abc@example.com']
$ews->mail->attach = ['file1', 'file2', 'file3']; //'file'
$ews->mail->send_as_email = 'abc@xyz.com';//to send as another user, not the logged in user. Optional

$ews->mail->send();  
```



## Create Draft
```
$ews->mail->sender_name = "Foo Bar";
$ews->mail->subject = "Test email";
$ews->mail->body = "This is a test email";
$ews->mail->recipient = 'abc@example.com'; //['abc@xyz.com', 'john.doe@example.com']
$ews->mail->recipient_name = "Amir Sanni";
$ews->mail->cc = ['abc@xyz.com', 'abc@example.com']; //'abc@example.com'
$ews->mail->bcc = 'abc@example.com'; //['abc@xyz.com', 'abc@example.com']
$ews->mail->attach = ['file1', 'file2', 'file3']; //'file'
$ews->mail->send_as_email = 'abc@xyz.com';

$ews->mail->save();  
```



## Get Messages
```
$ews->mail->limit = 30;

//each of the methods takes an optional page_number of type int
$ews->mail->inbox();//Messages in inbox
$ews->mail->sent(3);
$ews->mail->draft();
$ews->mail->outbox(1);
$ews->mail->conversationHistory();
$ews->mail->favourites();//favorites() will also work
$ews->mail->junk();
$ews->mail->deleted();
$ews->mail->archived();
```

## Get Contacts
```
$ews->contacts->limit = 10;

//Method takes an optional 'pageNumber' of type int
$res = $ews->contacts->get(); 
```

## Get Tasks
```
$ews->tasks->limit = 10;

//Method takes an optional 'pageNumber' of type int
$res = $ews->tasks->get();  
```


## Send Message From Draft
```
$ews->mail->limit = 30;

$draft_items = $ews->mail->draft();

if($draft_items->status === 1 && $draft_items->messages){
    foreach($draft_items->messages as $item){
        $ews->mail->send($item->message_id, $item->change_key);
    }
}

```


## Change Message Read Status
```
$ews->mail->limit = 30;

$items = $ews->mail->inbox();//$ews->mail->unread()

if($items->status === 1 && $items->messages){
    foreach($items->messages as $item){
        $ews->mail->markAsRead($item->message_id, $item->change_key);
        //$ews->mail->markAsUnread($item->message_id, $item->change_key);
    }
}

```


## Delete Messages
```
$ews->mail->limit = 30;

$items = $ews->mail->inbox();

if($items->status === 1 && $items->messages){
    foreach($items->messages as $item){
        $ews->mail->delete($item->message_id);
    }
}

```


## Create Calendar Event
```
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

```

Check out the examples folder for more usage information  
