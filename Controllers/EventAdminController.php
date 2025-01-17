<?php
require_once __DIR__ . '/../Models/Event.php';
require_once __DIR__ . '/../Models/EventsModel.php';
require_once __DIR__ . '/../Models/Address-Model.php';
require_once 'HomeController.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class EventAdminController{
    public function GetEvents(){
        $eventsModel = new EventsModel();
        $events = [];

        $EventList = $eventsModel->getNonDeletedEvents();

        foreach($EventList as $event){
            $wholeAddress="";
            $Address=new Address();
            $Address->GetWholeAddress($event->getLocationID(),$wholeAddress);

            $events[]=[
                'id' => $event->getId(),
                'name'=> $event->getName(),
                'date' => $event->getDateTime()->format('Y-m-d H:i:s'),
                'Address' => $wholeAddress
            ];
        }

        require_once __DIR__ . '/../Views/Admin_Dashboard.php';
    }

    public Function CreateEvent(){
        $model = new Address(); 
        $addressList = [];
        $error = null;

        if (!$model->GetWholeAddressesList($addressList)) {
            die("Failed to load address data.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and assign form inputs to variables
            $name = trim($_POST["name"]);
            $AddressID = $_POST["region"]; 
            $date = $_POST['date']; 
            $time = $_POST['time']; 
            $description = $_POST['description'];
            $max_attendees = $_POST['max_attendees'];
            $type = EventType::from($_POST['type']);

            $minDate = date('Y-m-d');
            if (new DateTime($date) < new DateTime($minDate)) {
                $error = "The event date cannot be in the past. Please select a valid future date.";
            }else{
                $eventTime = new DateTime($time);     
                $timeLimit = new DateTime('22:00:00');
                if ($eventTime >= $timeLimit) {
                    $error = "The event time must be before 10:00 PM.";
                }
            }

            $dateTimeStr = $date . ' ' . $time;
            $dateTime = new DateTime($dateTimeStr);

            $Event = new Event(0, $name, $AddressID, $dateTime, $description, $max_attendees, $type);

            $result = $Event->createEvent();

            if(!$result){
                $error ="Event Failed to get Created";
            }else{
                $homeController = new HomeController();
                $homeController->homeEventAdmin();
                exit();
            }

        }

        $data = [
            'addressList' => $addressList,
        ];
       require_once __DIR__ . '/../Views/Event_Creation.php';
       exit();
    }

    public function DeleteEvent($eventId){
        $Event = new Event(id: $eventId);
        return $Event->deleteEvent();
    }

    public function EditEvent(){
        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            $Event = new Event();
            $Event->readEvent($id);
            $eventAddress = $Event->getLocationID();

            $CityID = new Address();
            $CityID->read($eventAddress);
            $CityID = $CityID->getParentAddressID();

            $model = new Address(); 
            $addressList = [];

            if (!$model->GetWholeAddressesList($addressList)) {
                die("Failed to load address data.");
            }

            $EventDateTime = $Event->getDateTime();

            $event = [
                'id' => $Event->getId(),
                'name' => $Event->getName(),
                'DistrictID' => $eventAddress,
                'CityID' => $CityID,
                'date' => $EventDateTime->format('Y-m-d'),
                'time'=> $EventDateTime->format('H:i'),
                'description' => $Event->getDescription(),
                'max_attendees' => $Event->getMaxNoOfAttendance(),
                'type' => $Event->getType()->value,
                'addressList' => $addressList,
            ];

            require __DIR__ ."/../Views/Edit_Event.php";
        }
    }

    public function UpdateEvent(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and assign form inputs to variables
            $id = $_POST["id"];
            $name = trim($_POST["name"]);
            $AddressID = $_POST["region"]; 
            $date = $_POST['date']; 
            $time = $_POST['time']; 
            $description = $_POST['description'];
            $max_attendees = $_POST['max_attendees'];
            $type = EventType::from($_POST['type']);

            $minDate = date('Y-m-d');
            if (new DateTime($date) < new DateTime($minDate)) {
                $error = "The event date cannot be in the past. Please select a valid future date.";
            }else{
                $eventTime = new DateTime($time);     
                $timeLimit = new DateTime('22:00:00');
                if ($eventTime >= $timeLimit) {
                    $error = "The event time must be before 10:00 PM.";
                }
            }

            $dateTimeStr = $date . ' ' . $time;
            $dateTime = new DateTime($dateTimeStr);

            $Event = new Event($id);

            $result = $Event->UpdateEvent($name, $AddressID, $dateTime, $description, $max_attendees, $type);

            if(!$result){
                $error ="Event Failed to get Created";
            }else{
                $homeController = new HomeController();
                $homeController->homeEventAdmin();
                header("Location: http://localhost:3000/Controllers/EventAdminHomeController.php");
                exit();
            }

        }
    }

}

?>