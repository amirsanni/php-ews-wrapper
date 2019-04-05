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


# How to use
### Instantiate

```
use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('email', 'password', 'optionalServerAddress', 'optionalVersion');
```

**Note:** Server address defaults to _outlook.office365.com_  
**Supported Versions: 2007, 2009, 2010, 2013, 2016**. _Defaults to 2016_.



## Send Email
```
$mail->sender_name = "John Doe";
$mail->subject = "Test email";
$mail->message = "This is a test email";
$mail->recipient = 'abc@example.com'; //['abc@xyz.com', 'abc@example.com']
$mail->recipient_name = "Amir Sanni";
$mail->cc = ['abc@xyz.com', 'abc@example.com']; //'abc@example.com'
$mail->bcc = 'abc@example.com'; //['abc@xyz.com', 'abc@example.com']
$mail->attach = ['file1', 'file2', 'file3']; //'file'
$mail->send_as_email = 'abc@xyz.com';

$mail->send();  
```



## Create Draft
```
$mail->sender_name = "Foo Bar";
$mail->subject = "Test email";
$mail->message = "This is a test email";
$mail->recipient = 'abc@example.com'; //['abc@xyz.com', 'john.doe@example.com']
$mail->recipient_name = "Amir Sanni";
$mail->cc = ['abc@xyz.com', 'abc@example.com']; //'abc@example.com'
$mail->bcc = 'abc@example.com'; //['abc@xyz.com', 'abc@example.com']
$mail->attach = ['file1', 'file2', 'file3']; //'file'
$mail->send_as_email = 'abc@xyz.com';

$mail->createDraft();  
```



## Get Messages
```
$mail->limit = 30;

//each of the methods takes an optional page_number of type int
$mail->getInboxMessages();//Messages in inbox
$mail->getSentItems(3);
$mail->getDraftItems();
$mail->getOutboxItems(1);
$mail->getConversationHistory();
$mail->getFavourites();
$mail->getJunkItems();
$mail->getDeletedMessages();
$mail->getArchivedMessages();
$mail->getContacts();
$mail->getTasks();  
```


## Send Message From Draft
```
$mail->limit = 30;

$draft_items = $mail->getDraftItems();

if($draft_items->status === 1 && $draft_items->messages){
    foreach($draft_items->messages as $item){
        $mail->sendMessage($item->message_id, $item->change_key);
    }
}

```


## Change Message Read Status
```
$mail->limit = 30;

$items = $mail->getInboxMessages();//$mail->getUnreadMessages()

if($items->status === 1 && $items->messages){
    foreach($items->messages as $item){
        $mail->markAsRead($item->message_id, $item->change_key);//$mail->markAsUnread($item->message_id, $item->change_key);
    }
}

```


## Delete Messages
```
$mail->limit = 30;

$items = $mail->getInboxMessages();

if($items->status === 1 && $items->messages){
    foreach($items->messages as $item){
        $mail->deleteMessage($item->message_id);
    }
}

```

Check out the examples folder for more usage information  

Check out the examples folder for more usage information  