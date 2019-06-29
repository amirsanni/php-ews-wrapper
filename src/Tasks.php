<?php
namespace amirsanni\phpewswrapper;

use amirsanni\phpewswrapper\Folders;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;

class Tasks{
    public $limit;
    private $folder;

    public function __construct($ews){
        $this->folder = new Folders($ews);
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    public function get(int $page_number=1){
        $response = $this->folder->getItems($page_number, DistinguishedFolderIdNameType::TASKS, $this->limit);

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
}