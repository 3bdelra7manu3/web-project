<?php
/* ================================================
   AUTH.PHP - Handles login, signup, and logout
   ================================================ */

// Connect to our database by including config.php
require_once 'config.php';

// Figure out what the user wants to do (login, register, or logout)
// The form will send an 'action' field that tells us what to do
if(isset($_POST["action"])) {
    $action = $_POST["action"];
} else if(isset($_GET["action"])) {
    $action = $_GET["action"];
} else {
    $action = "";
}

echo "<!-- Processing action: $action -->";

// Handle forms submitted with POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ===== LOGIN PROCESS =====
    if ($action == "login") {
        echo "<!-- Processing login -->";
        
        // Get what the user typed in the form
        $username = $_POST["username"];
        $password = $_POST["password"];
        
        // Make sure they filled out both fields
        if ($username == "" || $password == "") {
            // They left something blank - send them back to try again
            header("Location: index.php?error=Please fill in all fields");
            exit();
        }
        
        // Look up the username in our database
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        // Prepare the query to prevent SQL injection
        $stmt = $conn->prepare($sql);
        // Bind the username parameter (s means string)
        $stmt->bind_param("s", $username);
        // Run the query
        $stmt->execute();
        // Get what the database returned
        $result = $stmt->get_result();
        
        // Check if we found a matching username
        if ($result->num_rows == 1) {
            // Username exists! Get the user's data
            $user = $result->fetch_assoc();
            
            // Check if password matches
            $password_correct = password_verify($password, $user["password"]);
            
            if ($password_correct) {
                // Login successful!
                // Start a session to remember the user
                session_start();
                
                // Save user info in the session
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                
                // Send them to the dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Wrong password
                header("Location: index.php?error=Invalid username or password");
                exit();
            }
        } else {
            // Username not found
            header("Location: index.php?error=Invalid username or password");
            exit();
        }
    }
    
    // ===== REGISTRATION PROCESS =====
    else if ($action == "register") {
        echo "<!-- Processing registration -->";
        
        // Get all the information from the registration form
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        
        // Check step 1: Make sure all fields are filled out
        if ($username == "" || $email == "" || $password == "" || $confirm_password == "") {
            // Something is missing - send them back
            header("Location: index.php?error=Please fill in all fields");
            exit();
        }
        
        // Check step 2: Make sure passwords match
        if ($password != $confirm_password) {
            header("Location: index.php?error=Your passwords don't match");
            exit();
        }
        
        // Check step 3: Make sure email looks valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: index.php?error=Please enter a valid email address");
            exit();
        }
        
        // Check step 4: See if username is already taken
        $check_username = "SELECT id FROM users WHERE username = ?";
        
        // Prepare query
        $stmt = $conn->prepare($check_username);
        
        // Bind parameter
        $stmt->bind_param("s", $username);
        
        // Run the query
        $stmt->execute();
        
        // Get results
        $username_result = $stmt->get_result();
        
        // If we found matches, username is taken
        if ($username_result->num_rows > 0) {
            header("Location: index.php?error=This username is already taken");
            exit();
        }
        
        // Check step 5: See if email is already used
        $check_email = "SELECT id FROM users WHERE email = ?";
        
        // Prepare query
        $stmt = $conn->prepare($check_email);
        
        // Bind parameter
        $stmt->bind_param("s", $email);
        
        // Run the query
        $stmt->execute();
        
        // Get results
        $email_result = $stmt->get_result();
        
        // If we found matches, email is already used
        if ($email_result->num_rows > 0) {
            header("Location: index.php?error=This email is already registered");
            exit();
        }
        
        // All checks passed! Let's create the account
        
        // First, make the password secure by hashing it
        // NEVER store plain passwords in the database!
        $secure_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Create SQL to insert new user
        $register_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        
        // Prepare query
        $stmt = $conn->prepare($register_sql);
        
        // Bind parameters (s means string)
        $stmt->bind_param("sss", $username, $email, $secure_password);
        
        // Try to create the account
        if ($stmt->execute()) {
            // Success! Account created
            header("Location: index.php?success=Your account has been created! You can now log in");
            exit();
        } else {
            // Something went wrong with the database
            header("Location: index.php?error=Something went wrong. Please try again.");
            exit();
        }
    }
    
    // End of POST request handling
}

// ===== LOGOUT PROCESS =====
// This happens when user clicks the logout link (which is a GET request with action=logout)
if ($action == "logout") {
    echo "<!-- Processing logout -->";
    
    // Start the session so we can clear it
    session_start();
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session completely
    session_destroy();
    
    // Send the user back to the login page
    header("Location: index.php?success=You have been logged out");
    exit();
}

// Make sure to close the database connection when we're done
$conn->close();
echo "<!-- Auth processing complete -->";

?>
