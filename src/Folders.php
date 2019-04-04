<?php
namespace amirsanni\phpewswrapper;

use jamesiarmes\PhpEws\Request\FindItemType;
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



class Folders{
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
                $det->message = $msg_info->Body->_;

                array_push($messages, $det);
            }

            $res->messages = $messages;
            $res->status = 1;
        }

        else{
            $res->messages = $response[0]->ResponseCode.": ".$response[0]->MessageText;
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

    public function getContacts(int $page_number){
        $response = $this->getFolderItems($page_number, DistinguishedFolderIdNameType::MY_CONTACTS);

        if($response[0]->ResponseClass == ResponseClassType::SUCCESS){
            //format the response by returning specific fields
            return $response;
        }

        else{
            return $response[0]->ResponseCode.": ".$response[0]->MessageText;
        }
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

        if($response[0]->ResponseClass == ResponseClassType::SUCCESS){
            //format the response by returning specific fields
            return $response;
        }

        else{
            return $response[0]->ResponseCode.": ".$response[0]->MessageText;
        }
    }
}