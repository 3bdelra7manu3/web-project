<?php
// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating users table: " . $conn->error);
}

// Create reminders table
$sql = "CREATE TABLE IF NOT EXISTS reminders (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    reminder_date DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating reminders table: " . $conn->error);
}

// Create appointments table
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(100) NOT NULL,
    doctor VARCHAR(100),
    location VARCHAR(100),
    notes TEXT,
    appointment_date DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating appointments table: " . $conn->error);
}

// Create medications table
$sql = "CREATE TABLE IF NOT EXISTS medications (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    instructions TEXT,
    remaining INT(11),
    refill_reminder BOOLEAN DEFAULT 0,
    refill_reminder_threshold INT(5) DEFAULT 5,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating medications table: " . $conn->error);
}

// Create medication logs table
$sql = "CREATE TABLE IF NOT EXISTS medication_logs (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    medication_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    taken_at DATETIME NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medication_id) REFERENCES medications(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating medication_logs table: " . $conn->error);
}
?>
