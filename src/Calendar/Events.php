<?php
namespace amirsanni\phpewswrapper\Calendar;

use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\AttendeeType;
use jamesiarmes\PhpEws\Type\CalendarItemType;
use jamesiarmes\PhpEws\Type\EmailAddressType;
use jamesiarmes\PhpEws\Request\CreateItemType;
use jamesiarmes\PhpEws\Enumeration\RoutingType;
use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttendeesType;
use jamesiarmes\PhpEws\Enumeration\CalendarItemCreateOrDeleteOperationType;

class Events{
    private $ews;
    private $request;
    public $event_start;
    public $event_end;
    public $timezone;
    public $location;
    public $subject;
    public $description;
    public $invitees;

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

    public function create(){
        // Build the request,
        $this->request = new CreateItemType();
        $this->request->SendMeetingInvitations = CalendarItemCreateOrDeleteOperationType::SEND_TO_ALL_AND_SAVE_COPY;//SEND_ONLY_TO_ALL
        $this->request->Items = new NonEmptyArrayOfAllItemsType();

        // Build the event to be added.
        $event = new CalendarItemType();
        $event->RequiredAttendees = new NonEmptyArrayOfAttendeesType();
        $event->Subject = $this->subject;
        $event->Location = $this->location;
        $event->BusyType = "Busy";

        //Set timezone
        $this->timezone ? date_default_timezone_set($this->timezone) : '';
        $event->Start = (new \DateTime($this->event_start))->format('c');
        $event->End = (new \DateTime($this->event_end))->format('c');

        // Set the event body.
        $event->Body = new BodyType();
        $event->Body->_ = $this->description;
        $event->Body->BodyType = BodyTypeType::HTML;

        // Add invitees if there are any
        if($this->invitees){
            foreach($this->invitees as $invitee) {
                $attendee = new AttendeeType();
                $attendee->Mailbox = new EmailAddressType();
                $attendee->Mailbox->EmailAddress = $invitee['email'];
                $attendee->Mailbox->Name = $invitee['name'];
                $attendee->Mailbox->RoutingType = RoutingType::SMTP;
                $event->RequiredAttendees->Attendee[] = $attendee;
            }
        }

        // Add the event to the request.
        $this->request->Items->CalendarItem[] = $event;
        
        $response = $this->ews->CreateItem($this->request);

        if($response->ResponseMessages->CreateItemResponseMessage[0]->ResponseClass == ResponseClassType::SUCCESS){
            return [
                'event_id'=>$response->ResponseMessages->CreateItemResponseMessage[0]->Items->CalendarItem[0]->ItemId->Id,
                'change_key'=>$response->ResponseMessages->CreateItemResponseMessage[0]->Items->CalendarItem[0]->ItemId->ChangeKey
            ];
        }

        return "Unable to create event";
    }
}