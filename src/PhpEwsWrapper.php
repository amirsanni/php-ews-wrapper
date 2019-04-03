<?php
namespace amirsanni\phpewswrapper;

/**
 * @author Amir Sanni <amirsanni@gmail.com>
 */

use jamesiarmes\PhpEws\Client;
use jamesiarmes\PhpEws\Request\CreateItemType;
use jamesiarmes\PhpEws\Request\CreateAttachmentType;
use jamesiarmes\PhpEws\Request\SendItemType;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
use jamesiarmes\PhpEws\Type\MessageType;
use jamesiarmes\PhpEws\Type\EmailAddressType;
use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\SingleRecipientType;
use jamesiarmes\PhpEws\Type\FileAttachmentType;
use jamesiarmes\PhpEws\Type\ItemIdType;
use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use jamesiarmes\PhpEws\Type\TargetFolderIdType;
use jamesiarmes\PhpEws\Enumeration\MessageDispositionType;

class PhpEwsWrapper {
    protected $ews;//ews connection client
    protected $version;

    private $sender;
    private $msg_obj;//phpews message object
    private $request;
    private $response;

    
    public $sender_name;
    public $recipient;
    public $recipient_name;
    public $cc;
    public $bcc;
    public $subject;
    public $message;
    public $attach;
    public $send_as_email;

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function __construct(string $email, string $password, string $server="outlook.office365.com", $version='2016'){
        try{
            $this->__setVersion($version);
            $this->ews = new Client($server, $email, $password, $this->version);
            $this->sender = $email;
        }

        catch(Exception $e){
            echo $e->getMessage();
        }
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setVersion($version){
        switch($version){
            case 2007:
                $this->version = Client::VERSION_2007;
                break;

            case 2009:
                $this->version = Client::VERSION_2009;
                break;

            case 2010:
                $this->version = Client::VERSION_2010;
                break;

            case 2013:
                $this->version = Client::VERSION_2013;
                break;

            default:
                $this->version = Client::VERSION_2016;
        }
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function send(){
        $this->__setAndCreateMessage();

        return $this->__sendMessage();
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function createDraft(){
        $this->__setAndCreateMessage(TRUE);

        if($this->attach){
            //attach files to the created message
            $message_id = $this->__getCreatedMessageId();

            $this->__attachFiles($message_id, $this->attach);
        }
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setAndCreateMessage($save_only=FALSE){
        $this->msg_obj = new MessageType();

        $this->__setSender();
        $this->__setSubject();
        $this->__setMsgBody();
        $this->__setItemType($save_only);
        $this->__setRecipient();
        $this->cc ? $this->__setCc() : "";
        $this->bcc ? $this->__setBcc() : "";

        //ADD MESSAGE OBJECT TO REQUEST DATA
        $this->request->Items->Message = $this->msg_obj;

        //Create Item
        $this->response = $this->ews->CreateItem($this->request);
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setSender(){
        $sender = new EmailAddressType();
        $sender->EmailAddress = $this->send_as_email ? $this->send_as_email : $this->sender;
        $sender->Name = $this->sender_name ? $this->sender_name : "";

        $this->msg_obj->From = new SingleRecipientType();
        $this->msg_obj->From->Mailbox = $sender;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setSubject(){
        $this->msg_obj->Subject = $this->subject;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setMsgBody(){
        $this->msg_obj->Body = new BodyType();
        $this->msg_obj->Body->BodyType = "HTML";
        $this->msg_obj->Body->_ = $this->message;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setItemType($save_only=FALSE){
        $this->request = new CreateItemType();
        $this->request->Items = new NonEmptyArrayOfAllItemsType();
        $this->request->MessageDisposition = $this->attach || $save_only ? MessageDispositionType::SAVE_ONLY : MessageDispositionType::SEND_AND_SAVE_COPY;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setCc(){
        $copy_email_addresses = new ArrayOfRecipientsType();

        $copy_email_addresses->Mailbox[] = new EmailAddressType();

        if(is_array($this->cc)){
            for($i = 0; $i < count($this->cc); $i++){
                $copy_email_addresses->Mailbox[$i]->EmailAddress = trim($this->cc[$i]);
            }
        }

        else{
            $copy_email_addresses->Mailbox[0]->EmailAddress = trim($this->cc);
        }

        //Add cc
        $this->msg_obj->CcRecipients = $copy_email_addresses;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setBcc(){
        $blind_copy_email_addresses = new ArrayOfRecipientsType();

        $blind_copy_email_addresses->Mailbox[] = new EmailAddressType();

        if(is_array($this->bcc)){
            for($i = 0; $i < count($this->bcc); $i++){
                $blind_copy_email_addresses->Mailbox[$i]->EmailAddress = trim($this->bcc[$i]);
            }
        }

        else{
            $blind_copy_email_addresses->Mailbox[0]->EmailAddress = trim($this->bcc);
        }

        //Add blind copy
        $this->msg_obj->BccRecipients = $blind_copy_email_addresses;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __setRecipient(){
        if(is_array($this->recipient)){
            foreach($this->recipient as $index=>$recipient){
                $email_recipients[$index] = new EmailAddressType();
                $email_recipients[$index]->EmailAddress = trim($recipient);
            }
        }

        else{
            $email_recipients[0] = new EmailAddressType();
            $email_recipients[0]->EmailAddress = trim($this->recipient);
            $email_recipients[0]->Name = trim($this->recipient_name);
        }

        //ADD RECIPIENT TO MESSAGE OBJECT
        $this->msg_obj->ToRecipients = $email_recipients;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __getCreatedMessageId(){
        return $this->response->ResponseMessages->CreateItemResponseMessage[0]->Items->Message[0]->ItemId->Id;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __sendMessage(){
        if($this->attach){			
            //add attachment and send
            $message_id = $this->__getCreatedMessageId();
            $change_key = $this->__attachFiles($message_id, $this->attach);

            return $this->__sendSavedMsg($message_id, $change_key);
        }

        else{
            return $this->response->ResponseMessages->CreateItemResponseMessage[0]->ResponseClass == ResponseClassType::SUCCESS;
        }
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    /**
     * Attach file to an existing message in draft and return the new changeKey
     */
    private function __attachFiles($message_id, $files){
        //ATTACH FILE(s)
        //Build the request
        $attach_request = new CreateAttachmentType();
        $attach_request->ParentItemId = new ItemIdType();
        $attach_request->ParentItemId->Id = $message_id;
        $attach_request->Attachments = new NonEmptyArrayOfAttachmentsType();
        
        //Build the file attachment(s).
        if(is_array($files)){
            foreach($files as $path){
                $file = new \SplFileObject($path);
                $finfo = finfo_open();

                $attachment = new FileAttachmentType();
                $attachment->Content = $file->openFile()->fread($file->getSize());
                $attachment->Name = $file->getBasename();
                $attachment->ContentType = finfo_file($finfo, $path);

                $attach_request->Attachments->FileAttachment[] = $attachment;
            }
        }

        else{
            $file = new \SplFileObject($files);
            $finfo = finfo_open();

            $attachment = new FileAttachmentType();
            $attachment->Content = $file->openFile()->fread($file->getSize());
            $attachment->Name = $file->getBasename();
            $attachment->ContentType = finfo_file($finfo, $files);
            
            $attach_request->Attachments->FileAttachment[] = $attachment;
        }

        //Attach the file to the message
        $attach_response = $this->ews->CreateAttachment($attach_request);
		
		//Get and return the new change key
		return $attach_response->ResponseMessages->CreateAttachmentResponseMessage[0]->Attachments->FileAttachment[0]->AttachmentId->RootItemChangeKey;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    /**
     * Send a message from draft
     */
    private function __sendSavedMsg($message_id, $change_key){
        //SEND THE MESSAGE
        $send_request = new SendItemType();
        $send_request->SaveItemToFolder = true;
        $send_request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
        
        // Add the message to the request.
        $item = new ItemIdType();
        $item->Id = $message_id;
        $item->ChangeKey = $change_key;
        $send_request->ItemIds->ItemId[] = $item;
        
        // Configure the folder to save the sent message to.
        $send_folder = new TargetFolderIdType();
        $send_folder->DistinguishedFolderId = new DistinguishedFolderIdType();
        $send_folder->DistinguishedFolderId->Id = DistinguishedFolderIdNameType::SENT;
		
        $send_request->SavedItemFolderId = $send_folder;
        
        //SEND
        $sent_response = $this->ews->SendItem($send_request);
		
		//get response message
		$response_messages = $sent_response->ResponseMessages->SendItemResponseMessage;
		
		//return success or failure
		return $response_messages[0]->ResponseClass == ResponseClassType::SUCCESS;
    }
}
