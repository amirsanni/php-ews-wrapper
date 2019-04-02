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

class PhpEwsWrapper {
    protected $ews;//ews connection client

    private $msg_obj;//phpews message object
    private $request;
    private $sender;

    
    public $sender_name;
    public $recipient;
    public $recipient_name;
    public $cc;
    public $bcc;
    public $subject;
    public $message;
    public $files;
    public $send_as_email;

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function __construct(string $email, string $password, string $server="outlook.office365.com"){
        try{
            $this->ews = new Client($server, $email, $password, Client::VERSION_2016);
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

    public function send(){
        $this->msg_obj = new MessageType();

        $this->__setSender();
        $this->__setSubject();
        $this->__setMsgBody();
        $this->__setItemType();
        $this->__setRecipient();
        $this->cc ? $this->__setCc() : "";
        $this->bcc ? $this->__setBcc() : "";
        
        return $this->__send();
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
        $sender->Name = $this->sender_name ?? "";

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

    private function __setItemType(){
        $this->request = new CreateItemType();
        $this->request->Items = new NonEmptyArrayOfAllItemsType();
        $this->request->MessageDisposition = $this->files ? "SaveOnly" : 'SendAndSaveCopy';
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

    private function __send(){
        //ADD MESSAGE OBJECT TO REQUEST DATA
        $this->request->Items->Message = $this->msg_obj;

        //Create Item
        $response = $this->ews->CreateItem($this->request);

        if($this->files){
            $message_id = $response->ResponseMessages->CreateItemResponseMessage[0]->Items->Message[0]->ItemId->Id;
			
            //add attachment and send
            return $this->sendSavedMsg($message_id);
        }

        else{
            return $response->ResponseMessages->CreateItemResponseMessage[0]->ResponseClass == ResponseClassType::SUCCESS;
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
     * Attach file to an existing message in draft and then send the message
     */
    private function sendSavedMsg($message_id){
        //ATTACH FILE(s)
        //Build the request
        $attach_request = new CreateAttachmentType();
        $attach_request->ParentItemId = new ItemIdType();
        $attach_request->ParentItemId->Id = $message_id;
        $attach_request->Attachments = new NonEmptyArrayOfAttachmentsType();
        
        //Build the file attachment(s).
        if(is_array($this->files)){
            foreach($this->files as $path){
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
            $file = new \SplFileObject($this->files);
            $finfo = finfo_open();

            $attachment = new FileAttachmentType();
            $attachment->Content = $file->openFile()->fread($file->getSize());
            $attachment->Name = $file->getBasename();
            $attachment->ContentType = finfo_file($finfo, $this->files);
            
            $attach_request->Attachments->FileAttachment[] = $attachment;
        }

        //Attach the file to the message
        $attach_response = $this->ews->CreateAttachment($attach_request);
		
		//Get the new change key
		$change_key = $attach_response->ResponseMessages->CreateAttachmentResponseMessage[0]->Attachments->FileAttachment[0]->AttachmentId->RootItemChangeKey;


        //NOW SEND THE MESSAGE
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