<?php
namespace amirsanni\phpewswrapper;

use jamesiarmes\PhpEws\Request\FindItemType;
use jamesiarmes\PhpEws\Request\FindFolderType;
use jamesiarmes\PhpEws\Type\IndexedPageViewType;
use jamesiarmes\PhpEws\Type\ItemResponseShapeType;
use jamesiarmes\PhpEws\Type\FolderResponseShapeType;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Enumeration\IndexBasePointType;
use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
use jamesiarmes\PhpEws\Enumeration\ItemQueryTraversalType;
use jamesiarmes\PhpEws\Enumeration\FolderQueryTraversalType;
use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseFolderIdsType;

class Folders{
    private $ews;

    public function __construct($ews){
        $this->ews = $ews;
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function get(){
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

    public function getItems(int $page_number, string $folder_name, int $limit=50){
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
        $offset = $page_number <= 1 ? 0 : ($page_number*$limit) - 1;

        $find->IndexedPageItemView = new IndexedPageViewType();
        $find->IndexedPageItemView->BasePoint = IndexBasePointType::BEGINNING;
        $find->IndexedPageItemView->Offset = $offset;
        $find->IndexedPageItemView->MaxEntriesReturned = $limit;
        
        $items = $this->ews->FindItem($find);
        
        // get returned results
        $returned_items = $items->ResponseMessages->FindItemResponseMessage;

        return $returned_items;
    }
}