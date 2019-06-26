<?php
namespace amirsanni\phpewswrapper;

class Events{
    protected $ews;
    protected $event_start;
    protected $event_end;
    protected $location;
    protected $subject;
    protected $busy;
    protected $invitees_email;

    public function __construct($ews_client){
        $this->ews = $ews_client;
    }



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
    }
}