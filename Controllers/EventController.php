<?php
require_once __DIR__ . '\..\Models\Event.php';
require_once __DIR__ . '\..\Models\EventsModel.php';
require_once __DIR__ . '\..\Models\Event-Participation.php';
require_once __DIR__ . '\..\Models\Volunteer-Model (1).php';
require_once __DIR__ . '\..\Models\Address-model.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class EventController{

    private static $user;

    public function __construct() {
        self::$user = isset($_SESSION['user']) ? unserialize($_SESSION['user']) : null;
    }

    public function HomeProfile(){
        $userController = new UserController();
        $userController->Display(self::$user, 'Volunteer');
    }

    public function volunteerDashBoard(){
        $volunteerId = self::$user->getID();
        $volunteerName=self::$user->getName();

        $data = [
            'volunteerName' => $volunteerName,
            'tasks' => [], // Initialize tasks array
        ];

        $EventModel = new EventsModel();
        $EventParticipation=new EventParticipation();

        $EventList = $EventModel->getEventsForVolunteer($volunteerId);
        foreach($EventList as $event){
            $eventId=$event->getId();
            if(!$EventParticipation->readEventParticipation(eventId:$eventId,volunteerId:$volunteerId)){
                throw new Exception("volunteer's cannot be getten");
            }
            $Role=$EventParticipation->getRole();

            $eventdata=[
                'id'=>$eventId,
                'Name'=>$event->getName(),
                'Date-Time'=>$event->getDateTime()->format('Y-m-d H:i:s'),
                'discription'=>$event->getDescription(),
                'Role'=>$Role,
            ];
           
            $data['tasks'][]=$eventdata;

        }

        require_once __DIR__ . '/../Views/Volunteer_Dashboard.php';

    }

    public function EventDetails(){
        


        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $eventId= $_POST['id'];
            $event=new Event();
            if(!$event->readEvent((int)$eventId)){
                throw new Exception("event's data cannot be displayed");

            }
            echo $eventId;
            $location=new Address();
            $name=$event->getName();
            $DateTime=$event->getDateTime();
            $locationId=$event->getLocationID();
            $description=$event->getDescription();
            $maxAttendees=$event->getMaxNoOfAttendance();
            $type=$event->getType()->value;

            $address='';

            $location->GetWholeAddress($locationId,$address);
                
            

            $Date = $DateTime->format('Y-m-d');
            $Time= $DateTime->format('H:i:s');

            $eventDetails=[
                'name'=>$name,
                'address'=>$address,
                'date'=>$Date,
                'time'=>$Time,
                'description'=>$description,
                'maxAttendees'=>$maxAttendees,
                'type'=>$type
            ];
           // $data['eventDetails']= $eventDetails;
           $data = [
            'eventDetails'=>$eventDetails
           ];

            require_once __DIR__ . '\..\Views\Event_Details.php';
            


        }

    }

}

?>