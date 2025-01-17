<?php
require_once __DIR__ . '/../Models/Event.php';
require_once __DIR__ . '/../Models/EventsModel.php';
require_once __DIR__ . '/../Models/Patient-Model.php';
require_once __DIR__ . '/../Models/Patient-need.php';
require_once __DIR__ . '/../Models/Patient-Event.php';
require_once __DIR__ . '/../Models/Address-Model.php';
require_once __DIR__ . '/../Models/Medical.php';
require_once 'HomeController.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class PatientController{
    private static $user;

    public function __construct() {
        self::$user = isset($_SESSION['user']) ? unserialize($_SESSION['user']) : null;
    }

    public function home(){
        $events = $this->GetEvents();
        $patientNeeds = $this->GetPatientNeeds();

        require_once __DIR__ . '/../Views/Patient_Dashboard.php';
    }

    public function GetEvents(){
        $id = self::$user->getID();
        $eventsModel = new EventsModel();
        $Patient_Event = new PatientEvent();
        $events = [];

        $EventList = $eventsModel->getNonDeletedEvents();

        foreach($EventList as $event){
            $wholeAddress="";
            $Address=new Address();
            $Address->GetWholeAddress($event->getLocationID(),$wholeAddress);

            $result = $Patient_Event->read($event->getID(), $id);


            $events[]=[
                'id' => $event->getId(),
                'name'=> $event->getName(),
                'date' => $event->getDateTime()->format('Y-m-d H:i:s'),
                'isRegistered' => $result,
                'location' => $wholeAddress
            ];
        }
        return $events;
    }

    public function GetPatientNeeds(){
        $id = self::$user->getID();
        $Patient = new Patient(id: $id);
        $PatientNeedsList = [];

        $PatientNeeds = $Patient->retrieveNeeds();

        foreach($PatientNeeds as $PatientNeed){
            $Medical=new Medical();

            $Medical->readMedical($PatientNeed->getMedicalID());

            $PatientNeedsList[]=[
                'name' => $Medical->getName(),
                'status'=> $PatientNeed->getStatus()->value,
            ];
        }

        return $PatientNeedsList;

    }

    public function registerEvent(){
        // Check if the request is a POST and contains JSON data
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = null;
            $inputData = file_get_contents('php://input');
            $data = json_decode($inputData, true);

            if (!isset($data['eventId']) || !isset($data['action'])) {
                echo json_encode(['success' => false, 'message' => 'Missing eventId or action']);
                exit();
            }

            $eventId = $data['eventId'];
            $action = $data['action'];

            $userId = self::$user->getID();

            // If user is not logged in, return an error
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'User not logged in']);
                exit();
            }

            // Create an instance of the Event model
            $eventModel = new Event(id: $eventId);
            $eventModel->readEvent($eventId);

            if ($action === 'register') {
                $result = $eventModel->addPatientToEvent($userId);
            } elseif ($action === 'unregister') {
                $result = $eventModel->deletePatientFromEvent($userId);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                exit();
            }

            // Check if the action was successful
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update registration status']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }

    public function addPatientNeed(){
        if (isset($_POST['medicalNames']) && $_POST['medicalNames'][0] !=null) {
            $id = self::$user->getID();

            $medicalNames = $_POST['medicalNames'];

            foreach ($medicalNames as $name) {
                if (!empty($name)) {
                    $medical = new Medical();
                    $medical->FindByName($name);
                    if($medical->getId() == null){
                        $medical->setName($name);
                        $medical->setType('Medicine');
                        $medical->createMedical();
                    }

                    $patientNeeds = new PatientNeed($medical->getId(), $id);
                    $patientNeeds->setStatus(NeedStatus::Waiting);
                    $patientNeeds->createPatientNeed();
                }
            }

            $homeController = new HomeController();
            $homeController->homePatient();
        }
    }

    public function deletePatientNeed(){
        if (isset($_GET['id'])) {
            $index = $_GET['id']; // The index passed from the URL
        
            $id = self::$user->getID(); // Get the patient ID
            $Patient = new Patient(id: $id);
        
            $PatientNeeds = $Patient->retrieveNeeds();
        
            // Make sure the index is valid
            if (isset($PatientNeeds[$index])) {
                $patientNeedAtIndex = $PatientNeeds[$index];
        
                // Perform the deletion
                $patientNeedAtIndex->deletePatientNeed();
        
                // Redirect to the patient's home page after deletion
                $homeController = new HomeController();
                $homeController->homePatient();
            } else {
                // Handle the case if the index is out of bounds
                echo "Invalid index or no need found at that index.";
            }
        }
    }        
}

?>