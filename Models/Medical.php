<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';
enum MedicalType: string {
    case MEDICINE = 'Medicine';
    case TOOL = 'Tool';
}

class Medical {
    private  $id = null;
    private  $name;
    private  $type;
    private  $quantity; 
    private  $IsDeleted;
    private  $dbProxy;

    public function __construct( $name = '', $type = "",  $quantity = 0,  $IsDeleted = false) {
        $this->name = $name;
        if($type!==""){
            $this->type = MedicalType::from($type);
        }else{
            $this->type=null;
        }

        $this->IsDeleted = $IsDeleted;
        $this->quantity = $quantity;
        $this->dbProxy = new DBProxy('user');
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        if($this->type ===null){return null;}
        return $this->type->value;
    }

  
    public function getQuantity() {
        return $this->quantity;
    }



    public function setQuantity( $quantity): void {
        $this->quantity = $quantity;
    }

    public function setName( $name): void {
        $this->name = $name;
    }

    public function setType( $type): void {
        $this->type = MedicalType::from($type);
    }

   


    public function createMedical(): bool {
       
        $query = "INSERT INTO Medical (Name, Type, Quantity, IsDeleted) VALUES (?, ?, ?, 0)"; 
        $stmt = $this -> dbProxy->prepare($query,[$this->name,$this->type->value,$this->quantity]);


        if (!$stmt) {
            return false;
        }
        $this->id = $this->dbProxy->getInsertId();
        return true;
       
    }

    public  function readMedical( $id): bool {
      

        $query = "SELECT  Name, Type, Quantity FROM Medical WHERE ID = ? AND IsDeleted=0";
        $stmt = $this -> dbProxy->prepare($query,[$id]);

        if (!$stmt) {
            return false;
        }
        $stmt->bind_result($name, $type, $quantity);

        if ($stmt->fetch()) {
            $this->id = $id;
            $this->name = $name;
            $this->type = MedicalType::from($type); // Convert string to MedicalType enum
            $this->quantity = $quantity;
            return true;
        }
    
        return false;
    }

    public function updateMedical(): bool {
        if (!$this->id) {
            throw new Exception("Cannot update a record without an ID.");
        }

      
        $query = "UPDATE Medical SET Name = ?, Type = ?, Quantity = ? WHERE ID = ?"; 
        $stmt = $this -> dbProxy->prepare($query,[$this->name,$this->type,$this->quantity,$this->id]);


        if (!$stmt) {
            return false;
        }

        return true;
    }

    public function incrementMedicalquantity($Medicalid, $amount){
        $query = "UPDATE Medical SET Quantity = Quantity + ? WHERE ID = ?"; 
        $stmt = $this -> dbProxy->prepare($query,[$amount,$Medicalid]);


        if (!$stmt) {
            return false;
        }

        return true;

    }


    public function deleteMedical(): bool {
        if (!$this->id) {
            throw new Exception("Cannot delete a record without an ID.");
        }

        $query = "UPDATE Medical SET IsDeleted =1 WHERE ID = ?";
        $stmt = $this -> dbProxy->prepare($query,[$this->id]);

        if (!$stmt) {
            return false;
        }
        return true;
  
    }
    public  function FindByName( $Medicalname): bool {
        $query = "SELECT ID, Name, Type, Quantity FROM Medical WHERE Name = ? AND IsDeleted = 0";
        $stmt = $this -> dbProxy->prepare($query,[$Medicalname]);

        if (!$stmt) {
            return false;
        }
        $stmt->bind_result($id, $name, $type, $quantity);

    if ($stmt->fetch()) {
        $this->id = $id;
        $this->name = $name;
        $this->type = MedicalType::from($type); // Convert string to MedicalType enum
        $this->quantity = $quantity;
        return true;
    }

    return false;
    }
}
?>