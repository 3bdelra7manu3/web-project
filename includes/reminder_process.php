<?php
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../index.php");
    exit;
}

// Process add reminder
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && ($_POST["action"] == "add" || $_POST["action"] == "update")) {
        // Get form data
        $title = $_POST["title"];
        $description = $_POST["description"];
        $reminder_date = $_POST["reminder_date"];
        $user_id = $_SESSION["id"];
        
        // Validate form data
        if (empty($title) || empty($reminder_date)) {
            header("Location: ../reminders.php?error=Please fill in all required fields");
            exit;
        }
        
        if ($_POST["action"] == "add") {
            // Insert new reminder
            $sql = "INSERT INTO reminders (user_id, title, description, reminder_date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $user_id, $title, $description, $reminder_date);
            
            if ($stmt->execute()) {
                header("Location: ../reminders.php?success=Reminder added successfully");
            } else {
                header("Location: ../reminders.php?error=Something went wrong");
            }
        } else if ($_POST["action"] == "update") {
            // Get reminder ID
            $id = $_POST["id"];
            
            // Check if reminder belongs to user
            $check_sql = "SELECT id FROM reminders WHERE id = ? AND user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $id, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows == 0) {
                header("Location: ../reminders.php?error=Unauthorized access");
                exit;
            }
            
            // Update reminder
            $sql = "UPDATE reminders SET title = ?, description = ?, reminder_date = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $title, $description, $reminder_date, $id, $user_id);
            
            if ($stmt->execute()) {
                header("Location: ../reminders.php?success=Reminder updated successfully");
            } else {
                header("Location: ../reminders.php?error=Something went wrong");
            }
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    // Delete reminder
    $id = $_GET["id"];
    $user_id = $_SESSION["id"];
    
    // Check if reminder belongs to user
    $check_sql = "SELECT id FROM reminders WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        header("Location: ../reminders.php?error=Unauthorized access");
        exit;
    }
    
    // Delete the reminder
    $sql = "DELETE FROM reminders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: ../reminders.php?success=Reminder deleted successfully");
    } else {
        header("Location: ../reminders.php?error=Something went wrong");
    }
} else {
    // Invalid request
    header("Location: ../reminders.php");
}

// Close connection
$conn->close();
?>
