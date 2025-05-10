# My Health Application Documentation

## Table of Contents
1. [Overview](#overview)
2. [Installation](#installation)
3. [Features](#features)
4. [File Structure](#file-structure)
5. [Database Structure](#database-structure)
6. [Bootstrap Classes Explained](#bootstrap-classes-explained)
7. [PHP Functions & Methods](#php-functions--methods)
8. [Security Considerations](#security-considerations)
9. [Future Enhancements](#future-enhancements)

## Overview

"My Health" is a personal health management web application built using PHP, MySQL, JavaScript, and Bootstrap. It helps users track various aspects of their health, including reminders, appointments, and medications.

## Installation

### Requirements
- XAMPP (or any server with PHP 7.0+ and MySQL)
- Web browser

### Setup Instructions
1. Install XAMPP on your computer
2. Clone or place the application files in the `c:\xampp\htdocs\health\` directory
3. Start the Apache and MySQL services in XAMPP control panel
4. Access the application through your browser: `http://localhost/health/`
5. The database will be automatically created on first access
6. Register for a new account and start using the application

## Features

### User Authentication
- User registration with email validation
- Secure login system with password hashing
- Session management for authenticated users

### Dashboard
- Overview of upcoming reminders and appointments
- Quick statistics on active medications, upcoming appointments, and reminders
- Medication status and dose tracking
- Refill alerts for medications running low

### Reminders
- Create, view, edit, and delete health reminders
- Set specific dates and times for reminders
- Add detailed descriptions for each reminder
- Track past and upcoming reminders

### Appointments
- Schedule appointments with healthcare providers
- Record appointment details including doctor, location, and notes
- Easily view upcoming appointments
- Keep history of past appointments

### Medications
- Track medications with dosage, frequency, and instructions
- Log when you take each medication dose
- Monitor remaining pill supply
- Get alerts when refills are needed
- View medication history
- Quick log doses from dashboard

## File Structure

### Main Files
- `index.php`: Login and registration page
- `dashboard.php`: Main dashboard with overview of health data
- `reminders.php`: For managing health reminders
- `appointments.php`: For managing doctor appointments
- `medications.php`: For managing medications and logging doses

### Includes Directory
- `db_connect.php`: Database connection script
- `create_tables.php`: Creates database tables if they don't exist
- `db_setup.php`: Initial database setup script
- `login_process.php`: Handles user login
- `register_process.php`: Handles user registration
- `reminder_process.php`: Processes reminder actions
- `appointment_process.php`: Processes appointment actions
- `medication_process.php`: Processes medication actions
- `logout.php`: Handles user logout

### Assets Directory
- `css/style.css`: Custom styling for the application

### JS Directory
- `app.js`: Contains JavaScript functionality for the application

## Database Structure

### Users Table
```sql
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

### Reminders Table
```sql
CREATE TABLE reminders (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    reminder_date DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

### Appointments Table
```sql
CREATE TABLE appointments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(100) NOT NULL,
    doctor VARCHAR(100),
    location VARCHAR(100),
    notes TEXT,
    appointment_date DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

### Medications Table
```sql
CREATE TABLE medications (
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
)
```

### Medication Logs Table
```sql
CREATE TABLE medication_logs (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    medication_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    taken_at DATETIME NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medication_id) REFERENCES medications(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

## Bootstrap Classes Explained

### Layout Classes
- `container`: Container for responsive layout with margins
- `row`: Creates a horizontal group of columns
- `col-*`: Column within a row (responsive grid)
- `col-md-*`: Column that adjusts based on medium screen sizes
- `col-sm-*`: Column that adjusts based on small screen sizes
- `col-lg-*`: Column that adjusts based on large screen sizes

### Spacing Classes
- `mt-*`: Margin top
- `mb-*`: Margin bottom
- `me-*`: Margin right (end)
- `ms-*`: Margin left (start)
- `my-*`: Margin top and bottom
- `mx-*`: Margin left and right
- `pt-*`: Padding top
- `pb-*`: Padding bottom
- `pe-*`: Padding right (end)
- `ps-*`: Padding left (start)
- `py-*`: Padding top and bottom
- `px-*`: Padding left and right
- `gap-*`: Gap between elements in flex/grid layouts

### Component Classes
- `card`: Creates a bordered box with padding
- `card-header`: Header part of a card
- `card-body`: Body part of a card
- `card-footer`: Footer part of a card
- `form-control`: Styling for form inputs
- `form-label`: Styling for form labels
- `form-check`: Styling for checkboxes and radio buttons
- `btn`: Basic button 
- `btn-primary`: Primary action button (blue)
- `btn-secondary`: Secondary action button (gray)
- `btn-success`: Success action button (green)
- `btn-danger`: Danger/delete button (red)
- `btn-warning`: Warning button (yellow)
- `btn-info`: Information button (light blue)
- `btn-outline-*`: Outlined version of buttons
- `alert`: Alert box
- `alert-success`: Success alert (green)
- `alert-danger`: Danger alert (red)
- `alert-warning`: Warning alert (yellow)
- `alert-info`: Information alert (blue)
- `table`: Styling for tables
- `table-hover`: Adds hover effect to table rows
- `badge`: Badge/label for highlighting
- `modal`: Modal dialog box
- `navbar`: Navigation bar
- `nav-tabs`: Tabbed navigation interface

### Utility Classes
- `text-center`: Center-aligns text
- `text-primary`, `text-secondary`, etc.: Text colors
- `bg-primary`, `bg-secondary`, etc.: Background colors
- `d-flex`: Display as flexbox
- `justify-content-*`: Justify content in flexbox
- `align-items-*`: Align items in flexbox
- `fw-bold`: Bold text
- `fs-*`: Font size
- `shadow`: Box shadow effect
- `rounded`: Rounded corners
- `d-grid`: Grid layout for buttons, etc.

## PHP Functions & Methods

### Database Functions
- `mysqli_connect()`: Establishes database connection
- `prepare()`: Prepares SQL statement for execution
- `bind_param()`: Binds variables to a prepared statement
- `execute()`: Executes a prepared statement
- `get_result()`: Gets a result set from a prepared statement
- `fetch_assoc()`: Fetches a result row as an associative array
- `query()`: Executes an SQL query

### Session Functions
- `session_start()`: Starts a new or resumes existing session
- `session_destroy()`: Destroys all data registered to a session

### String Functions
- `htmlspecialchars()`: Converts special characters to HTML entities
- `password_hash()`: Creates a password hash
- `password_verify()`: Verifies that a password matches a hash
- `nl2br()`: Inserts HTML line breaks before all newlines
- `date()`: Formats a local date and time

### Array Functions
- `count()`: Counts elements in an array
- `empty()`: Determines whether a variable is empty
- `isset()`: Determines if a variable is set and is not NULL

## Security Considerations

The application implements several security measures:

1. **Password Hashing**: All user passwords are hashed using PHP's password_hash function
2. **Prepared Statements**: SQL injection protection using prepared statements
3. **Session Validation**: Every page validates if the user is logged in
4. **Cross-Site Scripting (XSS) Protection**: User inputs are sanitized before output
5. **Form Validation**: Server-side validation of all form inputs

## Future Enhancements

Potential future enhancements for the application:

1. **Mobile App Version**: Creating a mobile app for more convenient access
2. **Health Metrics Tracking**: Adding ability to track weight, blood pressure, etc.
3. **Document Storage**: Storing health records and documents
4. **Calendar Integration**: Integration with Google/Apple calendars
5. **Push Notifications**: Real-time reminders for medications and appointments
6. **Doctor Directory**: Building a searchable doctor/healthcare provider directory
7. **Export/Import Data**: Allow users to export or import their health data
8. **Dark Mode**: Add a dark mode option for the UI
9. **Multi-language Support**: Add support for multiple languages
