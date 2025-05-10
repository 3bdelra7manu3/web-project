<?php
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../index.php");
    exit;
}

// Process add appointment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && ($_POST["action"] == "add" || $_POST["action"] == "update")) {
        // Get form data
        $title = $_POST["title"];
        $doctor = $_POST["doctor"];
        $location = $_POST["location"];
        $notes = $_POST["notes"];
        $appointment_date = $_POST["appointment_date"];
        $user_id = $_SESSION["id"];
        
        // Validate form data
        if (empty($title) || empty($appointment_date)) {
            header("Location: ../appointments.php?error=Please fill in all required fields");
            exit;
        }
        
        if ($_POST["action"] == "add") {
            // Insert new appointment
            $sql = "INSERT INTO appointments (user_id, title, doctor, location, notes, appointment_date) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", $user_id, $title, $doctor, $location, $notes, $appointment_date);
            
            if ($stmt->execute()) {
                header("Location: ../appointments.php?success=Appointment added successfully");
            } else {
                header("Location: ../appointments.php?error=Something went wrong");
            }
        } else if ($_POST["action"] == "update") {
            // Get appointment ID
            $id = $_POST["id"];
            
            // Check if appointment belongs to user
            $check_sql = "SELECT id FROM appointments WHERE id = ? AND user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $id, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows == 0) {
                header("Location: ../appointments.php?error=Unauthorized access");
                exit;
            }
            
            // Update appointment
            $sql = "UPDATE appointments SET title = ?, doctor = ?, location = ?, notes = ?, appointment_date = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", $title, $doctor, $location, $notes, $appointment_date, $id, $user_id);
            
            if ($stmt->execute()) {
                header("Location: ../appointments.php?success=Appointment updated successfully");
            } else {
                header("Location: ../appointments.php?error=Something went wrong");
            }
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    // Delete appointment
    $id = $_GET["id"];
    $user_id = $_SESSION["id"];
    
    // Check if appointment belongs to user
    $check_sql = "SELECT id FROM appointments WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        header("Location: ../appointments.php?error=Unauthorized access");
        exit;
    }
    
    // Delete the appointment
    $sql = "DELETE FROM appointments WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: ../appointments.php?success=Appointment deleted successfully");
    } else {
        header("Location: ../appointments.php?error=Something went wrong");
    }
} else {
    // Invalid request
    header("Location: ../appointments.php");
}

// Close connection
$conn->close();
?>
