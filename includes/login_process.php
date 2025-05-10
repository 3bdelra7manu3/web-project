<?php
require_once 'db_connect.php';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input values
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Validate input
    if (empty($username) || empty($password)) {
        header("Location: ../index.php?error=Please fill in all fields");
        exit();
    }
    
    // Check if user exists
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row["password"])) {
            // Password is correct, start a new session
            session_start();
            
            // Store data in session variables
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            
            // Redirect to dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            // Password is not valid
            header("Location: ../index.php?error=Invalid username or password");
            exit();
        }
    } else {
        // Username doesn't exist
        header("Location: ../index.php?error=Invalid username or password");
        exit();
    }
    
    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
