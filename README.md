# php-ews-wrapper
A simple wrapper for jamesiarmes/php-ews library


# Installation
```composer require amirsanni/php-ews-wrapper```


# Features
* Send Email


# How to use
_use amirsanni\phpewswrapper\PhpEwsWrapper;_

_$mail = new PhpEwsWrapper('email', 'password', 'optionalServerAddress');_

**Note:** Server address defaults to _outlook.office365.com_

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