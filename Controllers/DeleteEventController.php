<?php
require_once 'EventAdminController.php';  // Include the SignupController

$controller = new EventAdminController();
$controller->GetEvents();

if (isset($_GET['id'])) {
    $eventId = intval($_GET['id']); // Sanitize the event ID

    try {
        $controller = new EventAdminController();

        if ($controller->DeleteEvent($eventId)) {
            http_response_code(200); // Success
            echo json_encode(["message" => "Event deleted successfully."]);
        } else {
            http_response_code(400); // Failure
            echo json_encode(["message" => "Failed to delete the event."]);
        }
    } catch (Exception $e) {
        http_response_code(500); // Internal server error
        echo json_encode(["message" => "An error occurred.", "error" => $e->getMessage()]);
    }
} else {
    http_response_code(400); // Bad request
    echo json_encode(["message" => "No event ID provided."]);
}


?>