<?php
namespace amirsanni\phpewswrapper;

use jamesiarmes\PhpEws\Request\FindItemType;
use jamesiarmes\PhpEws\Request\SendItemType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseFolderIdsType;
use jamesiarmes\PhpEws\Type\ItemResponseShapeType;
use jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use jamesiarmes\PhpEws\Type\IndexedPageViewType;
use jamesiarmes\PhpEws\Enumeration\IndexBasePointType;
use jamesiarmes\PhpEws\Enumeration\ItemQueryTraversalType;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Request\GetItemType;
use jamesiarmes\PhpEws\Type\ItemIdType;
use jamesiarmes\PhpEws\Type\TargetFolderIdType;
use jamesiarmes\PhpEws\Request\CreateAttachmentType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
use jamesiarmes\PhpEws\Type\FileAttachmentType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
use jamesiarmes\PhpEws\Request\UpdateItemType;
use jamesiarmes\PhpEws\Type\ItemChangeType;
use jamesiarmes\PhpEws\Type\SetItemFieldType;
use jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
use jamesiarmes\PhpEws\Type\MessageType;
use jamesiarmes\PhpEws\Request\DeleteItemType;
use jamesiarmes\PhpEws\Enumeration\DisposalType;
use jamesiarmes\PhpEws\Request\FindFolderType;
use jamesiarmes\PhpEws\Enumeration\FolderQueryTraversalType;
use jamesiarmes\PhpEws\Type\FolderResponseShapeType;



class Messages{
    protected $ews;
    public $limit = 50;//number of items to return per page
    protected $folder_name;

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function __construct($ews_client){
        $this->ews = $ews_client;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function getFolderItems(int $page_number, string $folder_name){
        $find = new FindItemType();
        $find->ParentFolderIds = new NonEmptyArrayOfBaseFolderIdsType();
        $find->Traversal = ItemQueryTraversalType::SHALLOW;

        $find->ItemShape = new ItemResponseShapeType();
        $find->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;//get all properties of each item
        
        // Set the folder to get items from.
        $folder = new DistinguishedFolderIdType();
        $folder->Id = $folder_name;
        $find->ParentFolderIds->DistinguishedFolderId[] = $folder;
        
        // Set the start (offset) and limit based on page_number
        $offset = $page_number <= 1 ? 0 : ($page_number*$this->limit) - 1;

        $find->IndexedPageItemView = new IndexedPageViewType();
        $find->IndexedPageItemView->BasePoint = IndexBasePointType::BEGINNING;
        $find->IndexedPageItemView->Offset = $offset;
        $find->IndexedPageItemView->MaxEntriesReturned = $this->limit;
        
        $items = $this->ews->FindItem($find);
        
        // get returned results
        $returned_items = $items->ResponseMessages->FindItemResponseMessage;

        return $returned_items;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    private function __getMessageDetails($message_id){
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

    public function getFolders(){
        $request = new FindFolderType();
        $request->Traversal = FolderQueryTraversalType::DEEP;
        $request->FolderShape = new FolderResponseShapeType();
        $request->FolderShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;

        // configure the view
        $request->IndexedPageFolderView = new IndexedPageViewType();
        $request->IndexedPageFolderView->BasePoint = 'Beginning';
        $request->IndexedPageFolderView->Offset = 0;

        $request->ParentFolderIds = new NonEmptyArrayOfBaseFolderIdsType();

        // use a distinguished folder name to find folders inside it
        $request->ParentFolderIds->DistinguishedFolderId = new DistinguishedFolderIdType();
        $request->ParentFolderIds->DistinguishedFolderId->Id = DistinguishedFolderIdNameType::MESSAGE_ROOT;

        // request
        $response = $this->ews->FindFolder($request);

        $res = new \stdClass();

        if($response->ResponseMessages->FindFolderResponseMessage[0]->ResponseClass == ResponseClassType::SUCCESS){
            $retrieved_folders = $response->ResponseMessages->FindFolderResponseMessage[0]->RootFolder->Folders->Folder;

            $folders = [];

            foreach($retrieved_folders as $f){
                $det = new \stdClass();

                $det->name = $f->DisplayName;
                $det->id = $f->FolderId->Id;

                array_push($folders, $det);
            }

            $res->folders = $folders;
            $res->msg = 'success';
            $res->status = 1;
        }

        else{
            $res->status = 0;
            $res->msg = $response->ResponseMessages->FindFolderResponseMessage[0]->MessageText;
            $res->folders = [];
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

    public function getMessages(int $page_number, string $folder_name){
        $response = $this->getFolderItems($page_number, $folder_name);

        $res = new \stdClass();

        //format the response by returning specific fields
        if($response[0]->ResponseClass == ResponseClassType::SUCCESS){
            //proceed            
            $messages = [];
            $retrieved_messages = $response[0]->RootFolder->Items->Message;

            foreach($retrieved_messages as $msg){
                $msg_info = $this->__getMessageDetails($msg->ItemId->Id);

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

    public function getUnreadMessages(int $page_number){
        $response = $this->getMessages($page_number, DistinguishedFolderIdNameType::INBOX);

        $res = new \stdClass();

        if($response->status === 1 && $response->messages){
            $unread_messages = [];

            foreach($response->messages as $message){
                if($message->is_read){
                    continue;
                }

                array_push($unread_messages, $message);
            }

            $res->status = 1;
            $res->msg = "success";
            $res->messages = $unread_messages;

            return $res;
        }

        else if($response->status === 1){
            //no message in inbox
            $res->status = 0;
            $res->msg = "No message found";

            return $res;
        }

        else{
            //there is an error
            return $response;
        }
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function getContacts(int $page_number){
        $response = $this->getFolderItems($page_number, DistinguishedFolderIdNameType::MY_CONTACTS);

        $res = new \stdClass();

        if($response[0]->ResponseClass == ResponseClassType::SUCCESS){
            //format the response by returning specific fields
            $retrieved_contacts = $response[0]->RootFolder->Items->Contact;

            $res->contacts = $retrieved_contacts;
            $res->status = 1;
        }

        else{
            $res->msg = $response[0]->ResponseCode.": ".$response[0]->MessageText;
            $res->status = 0;
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

    public function getTasks(int $page_number){
        $response = $this->getFolderItems($page_number, DistinguishedFolderIdNameType::TASKS);

        $res = new \stdClass();

        if($response[0]->ResponseClass == ResponseClassType::SUCCESS){
            $retrieved_tasks = $response[0]->RootFolder->Items->Task;

            $tasks = [];

            foreach($retrieved_tasks as $task){
                $tsk = new \stdClass();

                $tsk->task_id = $task->ItemId->Id;
                $tsk->change_key = $task->ItemId->ChangeKey;
                $tsk->assigned_at = $task->AssignedTime;
                $tsk->delegated_by = $task->Delegator;
                $tsk->due_date = $task->DueDate;
                $tsk->is_team_task = $task->IsTeamTask;
                $tsk->task_owner = $task->Owner;
                $tsk->start_date = $task->StartDate;
                $tsk->status = $task->Status;
                $tsk->importance = $task->Importance;
                $tsk->last_modified_by = $task->LastModifiedName;
                $tsk->last_modified_at = $task->LastModifiedTime;
                $tsk->subject = $task->Subject;
                $tsk->flagged = (int)($task->Flag->FlagStatus != "NotFlagged");

                array_push($tasks, $tsk);
            }

            $res->tasks = $tasks;
            $res->status = 1;
            $res->msg = 'success';
        }

        else{
            $res->msg = $response[0]->ResponseCode.": ".$response[0]->MessageText;
            $res->status = 0;
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

    /**
     * Attach file to an existing message in draft and return the new changeKey
     */
    public function attachFiles($message_id, $files){
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
    public function sendSavedMsg($message_id, $change_key){
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