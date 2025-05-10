<?php
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../index.php");
    exit;
}

// Process medication actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new medication
    if (isset($_POST["action"]) && $_POST["action"] == "add") {
        // Get form data
        $name = $_POST["name"];
        $dosage = $_POST["dosage"];
        $frequency = $_POST["frequency"];
        $start_date = $_POST["start_date"];
        $end_date = !empty($_POST["end_date"]) ? $_POST["end_date"] : null;
        $instructions = $_POST["instructions"];
        $remaining = !empty($_POST["remaining"]) ? $_POST["remaining"] : null;
        $refill_reminder = isset($_POST["refill_reminder"]) ? 1 : 0;
        $refill_reminder_threshold = !empty($_POST["refill_reminder_threshold"]) ? $_POST["refill_reminder_threshold"] : 5;
        $user_id = $_SESSION["id"];
        
        // Validate form data
        if (empty($name) || empty($dosage) || empty($frequency) || empty($start_date)) {
            header("Location: ../medications.php?add=1&error=Please fill in all required fields");
            exit;
        }
        
        // Insert new medication
        $sql = "INSERT INTO medications (user_id, name, dosage, frequency, start_date, end_date, instructions, remaining, refill_reminder, refill_reminder_threshold) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssiis", $user_id, $name, $dosage, $frequency, $start_date, $end_date, $instructions, $remaining, $refill_reminder, $refill_reminder_threshold);
        
        if ($stmt->execute()) {
            header("Location: ../medications.php?success=Medication added successfully");
        } else {
            header("Location: ../medications.php?add=1&error=Something went wrong");
        }
    }
    // Update medication
    else if (isset($_POST["action"]) && $_POST["action"] == "update") {
        // Get form data
        $id = $_POST["id"];
        $name = $_POST["name"];
        $dosage = $_POST["dosage"];
        $frequency = $_POST["frequency"];
        $start_date = $_POST["start_date"];
        $end_date = !empty($_POST["end_date"]) ? $_POST["end_date"] : null;
        $instructions = $_POST["instructions"];
        $remaining = !empty($_POST["remaining"]) ? $_POST["remaining"] : null;
        $refill_reminder = isset($_POST["refill_reminder"]) ? 1 : 0;
        $refill_reminder_threshold = !empty($_POST["refill_reminder_threshold"]) ? $_POST["refill_reminder_threshold"] : 5;
        $user_id = $_SESSION["id"];
        
        // Validate form data
        if (empty($name) || empty($dosage) || empty($frequency) || empty($start_date)) {
            header("Location: ../medications.php?edit=$id&error=Please fill in all required fields");
            exit;
        }
        
        // Check if medication belongs to user
        $check_sql = "SELECT id FROM medications WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            header("Location: ../medications.php?error=Unauthorized access");
            exit;
        }
        
        // Update medication
        $sql = "UPDATE medications SET name = ?, dosage = ?, frequency = ?, start_date = ?, end_date = ?, 
                instructions = ?, remaining = ?, refill_reminder = ?, refill_reminder_threshold = ? 
                WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssiisii", $name, $dosage, $frequency, $start_date, $end_date, $instructions, 
                         $remaining, $refill_reminder, $refill_reminder_threshold, $id, $user_id);
        
        if ($stmt->execute()) {
            header("Location: ../medications.php?success=Medication updated successfully");
        } else {
            header("Location: ../medications.php?edit=$id&error=Something went wrong");
        }
    }
    // Update remaining pills
    else if (isset($_POST["action"]) && $_POST["action"] == "update_remaining") {
        $id = $_POST["id"];
        $remaining = $_POST["remaining"];
        $user_id = $_SESSION["id"];
        
        // Check if medication belongs to user
        $check_sql = "SELECT id FROM medications WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            header("Location: ../medications.php?error=Unauthorized access");
            exit;
        }
        
        // Update remaining pills
        $sql = "UPDATE medications SET remaining = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $remaining, $id, $user_id);
        
        if ($stmt->execute()) {
            header("Location: ../medications.php?view=$id&success=Medication supply updated");
        } else {
            header("Location: ../medications.php?view=$id&error=Something went wrong");
        }
    }
    // Log a medication dose
    else if (isset($_POST["action"]) && $_POST["action"] == "log_dose") {
        $medication_id = $_POST["medication_id"];
        $taken_at = $_POST["taken_at"];
        $notes = isset($_POST["notes"]) ? $_POST["notes"] : "";
        $user_id = $_SESSION["id"];
        
        // Check if medication belongs to user
        $check_sql = "SELECT id, remaining FROM medications WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $medication_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            header("Location: ../medications.php?error=Unauthorized access");
            exit;
        }
        
        // Get medication info
        $medication = $check_result->fetch_assoc();
        
        // Add log entry
        $log_sql = "INSERT INTO medication_logs (medication_id, user_id, taken_at, notes) VALUES (?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("iiss", $medication_id, $user_id, $taken_at, $notes);
        
        if ($log_stmt->execute()) {
            // Decrement remaining pills if tracking
            if ($medication["remaining"] !== null) {
                $new_remaining = max(0, $medication["remaining"] - 1);
                $update_sql = "UPDATE medications SET remaining = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_remaining, $medication_id);
                $update_stmt->execute();
            }
            
            header("Location: ../medications.php?view=$medication_id&success=Dose logged successfully");
        } else {
            header("Location: ../medications.php?view=$medication_id&error=Something went wrong");
        }
    }
    // Quick log (logs a dose immediately with current time)
    else if (isset($_POST["action"]) && $_POST["action"] == "quick_log") {
        $medication_id = $_POST["medication_id"];
        $taken_at = date("Y-m-d H:i:s");
        $user_id = $_SESSION["id"];
        
        // Check if medication belongs to user
        $check_sql = "SELECT id, remaining FROM medications WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $medication_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            header("Location: ../medications.php?error=Unauthorized access");
            exit;
        }
        
        // Get medication info
        $medication = $check_result->fetch_assoc();
        
        // Add log entry
        $log_sql = "INSERT INTO medication_logs (medication_id, user_id, taken_at, notes) VALUES (?, ?, ?, 'Quick logged')";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("iis", $medication_id, $user_id, $taken_at);
        
        if ($log_stmt->execute()) {
            // Decrement remaining pills if tracking
            if ($medication["remaining"] !== null) {
                $new_remaining = max(0, $medication["remaining"] - 1);
                $update_sql = "UPDATE medications SET remaining = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_remaining, $medication_id);
                $update_stmt->execute();
            }
            
            header("Location: ../medications.php?success=Dose logged successfully");
        } else {
            header("Location: ../medications.php?error=Something went wrong");
        }
    }
} 
// GET requests (for delete operations)
else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
        // Delete medication
        $id = $_GET["id"];
        $user_id = $_SESSION["id"];
        
        // Check if medication belongs to user
        $check_sql = "SELECT id FROM medications WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            header("Location: ../medications.php?error=Unauthorized access");
            exit;
        }
        
        // Delete all logs first
        $logs_sql = "DELETE FROM medication_logs WHERE medication_id = ?";
        $logs_stmt = $conn->prepare($logs_sql);
        $logs_stmt->bind_param("i", $id);
        $logs_stmt->execute();
        
        // Delete the medication
        $sql = "DELETE FROM medications WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $user_id);
        
        if ($stmt->execute()) {
            header("Location: ../medications.php?success=Medication deleted successfully");
        } else {
            header("Location: ../medications.php?error=Something went wrong");
        }
    }
    else if (isset($_GET["action"]) && $_GET["action"] == "delete_log" && isset($_GET["id"])) {
        // Delete medication log
        $log_id = $_GET["id"];
        $user_id = $_SESSION["id"];
        $medication_id = isset($_GET["medication_id"]) ? $_GET["medication_id"] : null;
        
        // Check if log belongs to user
        $check_sql = "SELECT id FROM medication_logs WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $log_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            header("Location: ../medications.php?error=Unauthorized access");
            exit;
        }
        
        // Delete the log
        $sql = "DELETE FROM medication_logs WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $log_id, $user_id);
        
        if ($stmt->execute()) {
            if ($medication_id) {
                header("Location: ../medications.php?view=$medication_id&success=Log entry deleted successfully");
            } else {
                header("Location: ../medications.php?success=Log entry deleted successfully");
            }
        } else {
            if ($medication_id) {
                header("Location: ../medications.php?view=$medication_id&error=Something went wrong");
            } else {
                header("Location: ../medications.php?error=Something went wrong");
            }
        }
    }
} else {
    // Invalid request
    header("Location: ../medications.php");
}

// Close connection
$conn->close();
?>
