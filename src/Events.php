<?php
namespace amirsanni\phpewswrapper;

<<<<<<< HEAD
class Events{
    protected $ews;
    protected $event_start;
    protected $event_end;
    protected $location;
    protected $subject;
    protected $busy;
    protected $invitees_email;
=======
use jamesiarmes\PhpEws\Type\CalendarEventDetails;
use jamesiarmes\PhpEws\Type\CalendarEvent;
use jamesiarmes\PhpEws\Request\CreateItemType;
use jamesiarmes\PhpEws\Enumeration\CalendarItemCreateOrDeleteOperationType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use jamesiarmes\PhpEws\Type\CalendarItemType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttendeesType;
use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\AttendeeType;
use jamesiarmes\PhpEws\Type\EmailAddressType;
use jamesiarmes\PhpEws\Enumeration\RoutingType;

class Events{
    private $ews;
    private $request;
    public $event_start;
    public $event_end;
    public $timezone;
    public $location;
    public $subject;
    public $event_body;
    public $invitees;

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
>>>>>>> 1.0

    public function __construct($ews_client){
        $this->ews = $ews_client;
    }

<<<<<<< HEAD


    public function create(){
        $request = new EWSType_CreateItemType();

        $event_details = new EWSType_CalendarEventDetails();

        $event = new EWSType_CalendarEvent();
        $event->CalendarEventDetails = $event_details;

        $event->Start = '2012-07-21T09:00:00+02:00';
        $event->End = '2012-07-21T18:00:00+02:00';
        $event->Subject = "Subject";
        $event->Location = "Location";
        $event->BusyType = "Busy";

        $request->Items->CalendarItem[] = $event;
        $request->SendMeetingInvitations = EWSType_CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;

        $response = $ews->CreateItem($request);
    }



    public function sendInvite(){
        //
=======
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
        $event->Body->_ = $this->event_body;
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
        
        print_r($this->ews->CreateItem($this->request));
>>>>>>> 1.0
    }
}