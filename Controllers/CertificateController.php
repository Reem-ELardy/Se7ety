<?php
require_once __DIR__ . '\..\Models\Event.php';
require_once __DIR__ . '\..\Models\EventsModel.php';
require_once __DIR__ . '\..\Models\Event-Participation.php';
require_once __DIR__ . '\..\Models\Volunteer-Model (1).php';
require_once __DIR__ .  '\..\Models\Certificate.php';
require_once __DIR__ . '\..\Models\Address-model.php';
require_once __DIR__ .  '\..\Models\CertificateToJSON.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class CertificateController{

    private static $user;

    public function __construct() {
        self::$user = isset($_SESSION['user']) ? unserialize($_SESSION['user']) : null;
    }

    public function VolunterData(){
        $volunteerId = self::$user->getID();
        $voi=new Volunteer();
        $voi->readVolunteer($volunteerId);
        $VolunteertotalHours=$voi->getVolunteerHours();
      
        
        $cretificateModel=new Certificate();

        $cretificates=$cretificateModel->getCertificatesByVolunteerId($volunteerId);
        $volunteerCertificates=[];
        

        foreach($cretificates as $cretificate){
         

            $eventId=$cretificate->geteventID();
           
        

          
                $event=new  Event();
                if($event->readEvent($eventId)){
                    $Event_name=$event->getName();
    
                    $Event_Date=$event->getDateTime()->format('Y-m-d H:i:s');
                    $address="";
                    $Address_Model=new Address();
                    $Address_Model->GetWholeAddress($event->getLocationID(),$address);
                    
                    $volunteerCertificates[] = [
                        'event' => $Event_name,
                        'date' => $Event_Date,
                        'address' => $address,
                        'downloadLink' => "Controllers/CertificateController.php?action=download&id={$eventId}",
                    ];


                }
        
        }
   
        $volunteerDetails=[
            'totalHours'=>$VolunteertotalHours,
            'certificates'=>$volunteerCertificates

        ];
        $data=[
            'volunteerDetails'=>$volunteerDetails,
        ];
       
        require_once __DIR__ . '\..\Views\Certificate.php';
    }

    public function DownloadCertifcate(){
        $volunteerId = self::$user->getID();
        if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['id'])) {
            $eventId =(int) $_GET['id'];
            $cretificate= new Certificate();
            $Certificates= $cretificate->getCertificatesByVolunteerId((int)$volunteerId);
            $vol=new Volunteer();
            $vol->readVolunteer((int)$volunteerId);
            $event=new Event();
            $event->readEvent($eventId);
            foreach($Certificates as $cer){
                if($cer->geteventID()===$eventId){
                    $cer->setVolunteerName($vol->getName());
                    $cer->setEventDate($event->getDateTime());
                    $cer->setEventName($event->getName());
                    $jsonAdapter = new CertificateToJSON($cer);
                    $cer->DownloadCertifcate();
                    break;

                }
            }
        }

                
    

    }
}

?>