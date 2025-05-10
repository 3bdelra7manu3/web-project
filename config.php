<?php
// Start the session so we can store user login info
session_start();

// Include language system
require_once 'lang/config.php';

// Setup our database info
$servername = "localhost";  // Database server (usually localhost for XAMPP)
$username = "root";         // Default XAMPP username
$password = "";             // Default XAMPP has no password
$dbname = "my_health";      // Our database name

// Connect to MySQL
// We don't select a database yet because it might not exist
$conn = new mysqli($servername, $username, $password);

// Check if we connected successfully
if ($conn->connect_error) {
    // If there's an error, stop the script and show the error
    die("Connection failed: " . $conn->connect_error);
}

// Now let's create our database if it doesn't exist already
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
// Run the query
$result = $conn->query($sql);
// Check if it worked
if ($result !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Now select our database to use
$conn->select_db($dbname);

// ------------------- CREATING TABLES ------------------- //

// 1. Create users table for storing account information
echo "<!-- Setting up database tables... -->";

$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, -- unique user ID
    username VARCHAR(50) NOT NULL UNIQUE,           -- login name
    password VARCHAR(255) NOT NULL,                 -- encrypted password
    email VARCHAR(100) NOT NULL,                    -- user's email
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP   -- when account was created
)";

// Execute the query to create users table
$result = $conn->query($users_table);
// Check if it worked
if ($result !== TRUE) {
    echo "<p>There was a problem creating the users table</p>";
    die("Error creating users table: " . $conn->error);
}

// 2. Create appointments table for doctor visits
$appointments_table = "CREATE TABLE IF NOT EXISTS appointments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, -- unique appointment ID
    user_id INT(11) NOT NULL,                       -- which user this belongs to
    title VARCHAR(100) NOT NULL,                    -- appointment name
    doctor VARCHAR(100),                            -- doctor's name
    location VARCHAR(100),                          -- address or hospital name
    notes TEXT,                                     -- additional info
    appointment_date DATETIME NOT NULL,             -- when is the appointment
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,  -- when this was added
    FOREIGN KEY (user_id) REFERENCES users(id)      -- link to user table
)";

// Execute the query to create appointments table
$result = $conn->query($appointments_table);
// Check if it worked
if ($result !== TRUE) {
    echo "<p>There was a problem creating the appointments table</p>";
    die("Error creating appointments table: " . $conn->error);
}

// 3. Create medications table for tracking medicine
$medications_table = "CREATE TABLE IF NOT EXISTS medications (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, -- unique medication ID
    user_id INT(11) NOT NULL,                       -- which user this belongs to
    name VARCHAR(100) NOT NULL,                     -- medicine name
    dosage VARCHAR(50) NOT NULL,                    -- how much to take (e.g., 10mg)
    frequency VARCHAR(100) NOT NULL,                -- how often to take it
    start_date DATE NOT NULL,                       -- when to start taking it
    end_date DATE,                                  -- when to stop (if applicable)
    instructions TEXT,                              -- special instructions
    remaining INT(11),                              -- pills remaining
    refill_reminder BOOLEAN DEFAULT 0,              -- should remind about refill?
    refill_reminder_threshold INT(5) DEFAULT 5,     -- when to remind (pills left)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,  -- when this was added
    FOREIGN KEY (user_id) REFERENCES users(id)      -- link to user table
)";

// Execute the query to create medications table
$result = $conn->query($medications_table);
// Check if it worked
if ($result !== TRUE) {
    echo "<p>There was a problem creating the medications table</p>";
    die("Error creating medications table: " . $conn->error);
}

// 4. Create medication logs table for tracking when medicine was taken
$medication_logs_table = "CREATE TABLE IF NOT EXISTS medication_logs (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, -- unique log ID
    medication_id INT(11) NOT NULL,                 -- which medication was taken
    user_id INT(11) NOT NULL,                       -- which user took it
    taken_at DATETIME NOT NULL,                     -- when it was taken
    notes TEXT,                                     -- any notes about taking it
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,  -- when this log was created
    FOREIGN KEY (medication_id) REFERENCES medications(id), -- link to medication
    FOREIGN KEY (user_id) REFERENCES users(id)      -- link to user
)";

// Execute the query to create medication logs table
$result = $conn->query($medication_logs_table);
// Check if it worked
if ($result !== TRUE) {
    echo "<p>There was a problem creating the medication logs table</p>";
    die("Error creating medication_logs table: " . $conn->error);
}

echo "<!-- Database setup complete -->";

?>
