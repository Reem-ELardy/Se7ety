<?php

enum DonationType: string {
    case Medical = 'Medical';
    case Money = 'Money';
}

class Donation {
    protected IDonationMethodStrategy $donationMethod;
    private ?int $id = null; // Allow null value
    protected DonationType $donationtype;
    private ?float $cashamount = null; // Allow null value

    public function __construct(IDonationMethodStrategy $donationMethod, DonationType $donationtype, ?float $cashamount = null) {
        $this->donationMethod = $donationMethod;
        $this->donationtype = $donationtype;
        $this->cashamount = $cashamount; // Optional, defaults to null
    


        // Ensure cash amount is only set for Medical donations
        if ($donationtype === DonationType::Medical && $cashamount !== null) {
            $this->cashamount = $cashamount;
        } elseif ($donationtype === DonationType::Money && $cashamount !== null) {
            throw new Exception("Cash amount should not be set for Money donations.");
        }
    }

    public function process(float $amount, int $quantity, string $itemDescription): void {
        $this->donationMethod->processDonation($amount, $quantity, $itemDescription);
    }


    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getCashAmount(): ?float {
        return $this->cashamount;
    }

    public function setCashAmount(?float $cashamount): void {
        if ($this->donationtype === DonationType::Medical) {
            $this->cashamount = $cashamount;
        } else {
            throw new Exception("Cash amount can only be set for Medical donations.");
        }
    }
    public function getDonationMethod(): ?IDonationMethodStrategy {
        return $this->donationMethod;
    }

    public function getDonationType(): DonationType {
        return $this->donationtype;
    }
    public function setDonationType(DonationType $donationType): void {
        $this->donationtype = $donationType;
    }

    public static function Donate(DonationType $type, string $method, $additionalParams = []): Donation {
        // Choose donation type
        if ($type === DonationType::Medical) {
            $donationMethod = new InKindDonation();
            $donation = new MedicalDonation($donationMethod);

            // Pass cash amount if provided
            if (isset($additionalParams['cashamount'])) {
                $donation->setCashAmount($additionalParams['cashamount']);
            }
        } elseif ($type === DonationType::Money) {
            // Choose payment method
            switch ($method) {
                case "Cash":
                    $donationMethod = new CashDonation();
                    break;
                case "Check":
                    $donationMethod = new CheckDonation(
                        $additionalParams['checkNumber'],
                        $additionalParams['expirationDate'],
                        $additionalParams['bankName']
                    );
                    break;
                case "EWallet":
                    $donationMethod = new EWalletDonation($additionalParams['transactionID']);
                    break;
                default:
                    throw new Exception("Invalid payment method.");
            }
            $donation = new MoneyDonation($donationMethod);
        } else {
            throw new Exception("Invalid donation type.");
        }

        return $donation;
    }

    public function createDonation() {
        // Get the database connection from the singleton instance
        $conn = DBConnection::getInstance()->getConnection();

        // Insert the Donation record
        $query = "INSERT INTO DONATION (ID, Type, CashAmount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $id = $this->id;
        $type = $this->donationtype->value;
        $cashamount = $this->cashamount;

        // Bind parameters and execute the query
        $stmt->bind_param("isi", $id, $type, $cashamount);
        $result = $stmt->execute();

        if ($result) {
            // Set the donation ID
            $this->id = $conn->insert_id;
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }



 // Read a donation from the database by ID
 public static function readDonation(int $donationId): ?donation {
    $conn = DBConnection::getInstance()->getConnection();
    $query = "SELECT ID, Type, CashAmount FROM Donation WHERE ID = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $donationId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $donationMethod = self::createDonationMethodFromType($row['Type']);
        $donation = new self($donationMethod, DonationType::from($row['Type']));
        $donation->setId($row['ID']);
        if ($row['Type'] === DonationType::Medical->value) {
            $donation->setCashAmount((float)$row['CashAmount']);
        }
        return $donation;
    } else {
        return null; // No donation found
    }
}



 // Update an existing donation in the database
 public function updateDonation(): bool {
    $conn = DBConnection::getInstance()->getConnection();
    $query = "UPDATE DONATION SET Type = ?, CashAmount = ? WHERE ID = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $type = $this->donationtype->value;
    $cashamount = $this->cashamount;
    $id = $this->id;

    $stmt->bind_param("sdi", $type, $cashamount, $id);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $result;
}

// Delete a donation from the database by ID
public function deleteDonation(): bool {
    $conn = DBConnection::getInstance()->getConnection();
    $query = "DELETE FROM DONATION WHERE ID = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $id = $this->id;
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $result;
}

// Helper method to create the appropriate donation method strategy
private static function createDonationMethodFromType(string $type): IDonationMethodStrategy {
    return match ($type) {
        DonationType::Medical->value => new InKindDonation(),
        DonationType::Money->value => new CashDonation(), // Default for money donations
        default => throw new Exception("Unknown donation type."),
    };
}
}

?>