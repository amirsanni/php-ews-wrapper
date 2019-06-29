<?php
namespace amirsanni\phpewswrapper\Messages;

use amirsanni\phpewswrapper\Folders;
use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\ItemIdType;
use jamesiarmes\PhpEws\Type\MessageType;
use jamesiarmes\PhpEws\Request\GetItemType;
use jamesiarmes\PhpEws\Type\ItemChangeType;
use jamesiarmes\PhpEws\Request\SendItemType;
use jamesiarmes\PhpEws\Type\EmailAddressType;
use jamesiarmes\PhpEws\Type\SetItemFieldType;
use jamesiarmes\PhpEws\Request\CreateItemType;
use jamesiarmes\PhpEws\Request\DeleteItemType;
use jamesiarmes\PhpEws\Request\UpdateItemType;
use jamesiarmes\PhpEws\Type\FileAttachmentType;
use jamesiarmes\PhpEws\Type\TargetFolderIdType;
use jamesiarmes\PhpEws\Enumeration\DisposalType;
use jamesiarmes\PhpEws\Type\SingleRecipientType;
use jamesiarmes\PhpEws\Type\ItemResponseShapeType;
use jamesiarmes\PhpEws\Request\CreateAttachmentType;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
use jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
use jamesiarmes\PhpEws\Enumeration\MessageDispositionType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;

class Message{
    private $folder;

    protected $ews;
    protected $msg;
    protected $request;
    protected $response;

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function __construct($ews){
        $this->folder = new Folders($ews);
        $this->ews = $ews;
        $this->msg = new MessageType();
    }    

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function setSender(string $sender_email, string $sender_name='', string $send_as_email=''){
        $sender = new EmailAddressType();
        $sender->EmailAddress = $send_as_email ? $send_as_email : $sender_email;
        $sender->Name = $sender_name ? $sender_name : "";

        $this->msg->From = new SingleRecipientType();
        $this->msg->From->Mailbox = $sender;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function setSubject(string $subject){
        $this->msg->Subject = $subject;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function setMsgBody(string $msg_body){
        $this->msg->Body = new BodyType();
        $this->msg->Body->BodyType = "HTML";
        $this->msg->Body->_ = $msg_body;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function setItemType($attach='', $save_only=FALSE){
        $this->request = new CreateItemType ();
        $this->request->Items = new NonEmptyArrayOfAllItemsType();
        $this->request->MessageDisposition = $attach || $save_only ? MessageDispositionType::SAVE_ONLY : MessageDispositionType::SEND_AND_SAVE_COPY;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function setCc($cc){
        $copy_email_addresses = new ArrayOfRecipientsType();

        if(is_array($cc)){
            for($i = 0; $i < count($cc); $i++){
                $copy_email_addresses->Mailbox[$i] = new EmailAddressType();
                $copy_email_addresses->Mailbox[$i]->EmailAddress = trim($cc[$i]);
            }
        }

        else{
            $copy_email_addresses->Mailbox[0] = new EmailAddressType();
            $copy_email_addresses->Mailbox[0]->EmailAddress = trim($cc);
        }

        //Add cc
        $this->msg->CcRecipients = $copy_email_addresses;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function setBcc($bcc){
        $blind_copy_email_addresses = new ArrayOfRecipientsType();

        if(is_array($bcc)){
            for($i = 0; $i < count($this->bcc); $i++){
                $blind_copy_email_addresses->Mailbox[$i] = new EmailAddressType();
                $blind_copy_email_addresses->Mailbox[$i]->EmailAddress = trim($bcc[$i]);
            }
        }

        else{
            $blind_copy_email_addresses->Mailbox[0] = new EmailAddressType();
            $blind_copy_email_addresses->Mailbox[0]->EmailAddress = trim($bcc);
        }

        //Add blind copy
        $this->msg->BccRecipients = $blind_copy_email_addresses;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function setRecipients($recipients, string $recipient_name=''){
        if(is_array($recipients)){
            foreach($recipients as $index=>$recipient){
                $email_recipients[$index] = new EmailAddressType();
                $email_recipients[$index]->EmailAddress = trim($recipient);
            }
        }

        else{
            $email_recipients[0] = new EmailAddressType();
            $email_recipients[0]->EmailAddress = trim($recipients);
            $email_recipients[0]->Name = trim($recipient_name);
        }

        //ADD RECIPIENT TO MESSAGE OBJECT
        $this->msg->ToRecipients = $email_recipients;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function setReplyTo(string $reply_to_email){
        $reply_to[0] = new EmailAddressType();
        $reply_to[0]->EmailAddress = trim($reply_to_email);

        //ADD reply_to TO MESSAGE OBJECT
        $this->msg->ReplyTo = $reply_to;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function createMessage(){
        //ADD MESSAGE OBJECT TO REQUEST DATA
        $this->request->Items->Message = $this->msg;

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

    protected function getCreatedMessageId(){
        return $this->response->ResponseMessages->CreateItemResponseMessage[0]->Items->Message[0]->ItemId->Id;
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
    protected function attachFiles(string $message_id, $files){
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
    protected function sendSavedMsg($message_id, $change_key){
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

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function getMessages(int $page_number, string $folder_name, int $limit){
        $response = $this->folder->getItems($page_number, $folder_name, $limit);

        $res = new \stdClass();

        //format the response by returning specific fields
        if($response[0]->ResponseClass == ResponseClassType::SUCCESS){
            //proceed
            $messages = [];
            $retrieved_messages = $response[0]->RootFolder->Items->Message;

            foreach($retrieved_messages as $msg){
                $msg_info = $this->getMessageDetails($msg->ItemId->Id);

                $det = new \stdClass();

                $det->message_id = $msg->ItemId->Id;
                $det->change_key = $msg->ItemId->ChangeKey;
                $det->subject = $msg->Subject;
                $det->sender = $msg_info->Sender->Mailbox->EmailAddress ? $msg_info->Sender->Mailbox->EmailAddress : $msg_info->From->Mailbox->EmailAddress;
                $det->recipients = $msg_info->ToRecipients && $msg_info->ToRecipients->Mailbox ? implode(", ", array_column($msg_info->ToRecipients->Mailbox, 'EmailAddress')) : "";
                $det->cc = $msg_info->CcRecipients && $msg_info->CcRecipients->Mailbox ? implode(", ", array_column($msg_info->CcRecipients->Mailbox, 'EmailAddress')) : "";
                $det->bcc = $msg_info->BccRecipients && $msg_info->BccRecipients->Mailbox ? implode(", ", array_column($msg_info->BccRecipients->Mailbox, 'EmailAddress')) : "";
                $det->date_sent = $msg->DateTimeSent;
                $det->date_received = $msg->DateTimeReceived;
                $det->flagged = (int)($msg->Flag->FlagStatus != "NotFlagged");
                $det->attachments = $msg_info->Attachments && $msg_info->Attachments->FileAttachment ? implode(", ", array_column($msg_info->Attachments->FileAttachment, 'Name')) : "";
                $det->is_read = $msg_info->IsRead;
                $det->message = $msg_info->Body->_;

                array_push($messages, $det);
            }

            $res->messages = $messages;
            $res->status = 1;
            $res->msg = "success";
        }

        else{
            $res->msg = $response[0]->ResponseCode.": ".$response[0]->MessageText;
            $res->status = 0;
            $res->messages = '';
        }

        return $res;
    }    

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function getMessageDetails($message_id){
        $fetch_request = new GetItemType();

        $fetch_request->ItemShape = new ItemResponseShapeType();
        $fetch_request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;

        $item = new ItemIdType();
        $item->Id = $message_id;
        $fetch_request->ItemIds->ItemId[] = $item;
        
        //GET
        $response = $this->ews->GetItem($fetch_request);
        
        $response_msg = $response->ResponseMessages->GetItemResponseMessage[0];

        if($response_msg->ResponseClass == 'Success'){
            return $response_msg->Items->Message[0];
        }

        else{
            return $response_msg->ResponseMessages->GetItemResponseMessage->MessageText;
        }
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    protected function updateMessageReadStatus(string $message_id, string $change_key, string $read_status){
        $request = new UpdateItemType();
        $request->MessageDisposition = 'SaveOnly';
        $request->ConflictResolution = 'AlwaysOverwrite';
        $request->ItemChanges = [];

        $change = new ItemChangeType();
        $change->ItemId = new ItemIdType();
        $change->ItemId->Id = $message_id;
        $change->ItemId->ChangeKey = $change_key;

        $field = new SetItemFieldType();
        $field->FieldURI = new PathToUnindexedFieldType();
        $field->FieldURI->FieldURI = 'message:IsRead';
        $field->Message = new MessageType();
        $field->Message->IsReadSpecified = $read_status == 'read' ? TRUE : FALSE;
        $field->Message->IsRead = $read_status == 'read' ? TRUE : FALSE;

        $change->Updates->SetItemField[] = $field;

        $request->ItemChanges[] = $change;

        $response = $this->ews->UpdateItem($request);
        
        return $response->ResponseMessages->UpdateItemResponseMessage[0]->ResponseClass == ResponseClassType::SUCCESS;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function deleteMessage(string $message_id){
        $request = new DeleteItemType();
        $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
        $request->ItemIds->ItemId = new ItemIdType();
        $request->ItemIds->ItemId->Id = $message_id; 

        $request->DeleteType = new DisposalType();
        $request->DeleteType = DisposalType::MOVE_TO_DELETED_ITEMS;

        $response = $this->ews->DeleteItem($request);

        return $response->ResponseMessages->DeleteItemResponseMessage[0]->ResponseClass == ResponseClassType::SUCCESS;
    }
}