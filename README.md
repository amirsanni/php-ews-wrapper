# php-ews-wrapper
A simple wrapper for jamesiarmes/php-ews library


# Installation
```composer require amirsanni/php-ews-wrapper```


# Features
* Send Email
* Create Draft


# How to use
### Instantiate

```
use amirsanni\phpewswrapper\PhpEwsWrapper;

$mail = new PhpEwsWrapper('email', 'password', 'optionalServerAddress');
```

**Note: Server address defaults to outlook.office365.com**


### Send Email
```
**$mail->sender_name = "SenderName";**  
**$mail->subject = "Subject";**  
**$mail->message = "Test email";**  
**$mail->recipient = 'String' or [Array];**  
**$mail->recipient_name = "Amir Sanni";**  
**$mail->cc = 'String' or [Array];**  
**$mail->bcc = 'String' or [Array];**  
**$mail->attach = 'String' or [Array];**  
**$mail->send_as_email = 'String';**  

**$mail->send();**  
```



### Create Draft
```
$mail->sender_name = "SenderName";
$mail->subject = "Subject";
$mail->message = "Test email";
$mail->recipient = 'String' or [Array];
$mail->recipient_name = "Amir Sanni";
$mail->cc = 'String' or [Array];
$mail->bcc = 'String' or [Array];
$mail->attach = 'String' or [Array];
$mail->send_as_email = 'String';

$mail->createDraft();
```