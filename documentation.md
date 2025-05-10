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

"My Health" is a simplified personal health management web application built using PHP, MySQL, JavaScript, and Bootstrap. It helps users track various aspects of their health, focusing on appointments and medications. The application has been designed with a beginner-friendly code structure for easy understanding and maintenance.

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
- Combined authentication system in a single file for simplified management

### Dashboard
- Overview of upcoming appointments
- Quick statistics on active medications and upcoming appointments
- Medication status and dose tracking
- Refill alerts for medications running low

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
- `config.php`: Database connection and table setup
- `auth.php`: Combined file handling login, registration, and logout
- `dashboard.php`: Main dashboard with overview of health data
- `appointments.php`: For managing doctor appointments (integrated processing)
- `medications.php`: For managing medications and logging doses (integrated processing)

### Assets Directory
- `assets/css/`: Contains stylesheets for the application
- `assets/js/app.js`: Contains JavaScript functionality for the application

## Database Structure

The application uses a simple database structure with three main tables. The database is automatically created and tables are set up when you first access the application through the `config.php` file.

### Users Table
```sql
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Securely hashed passwords
    email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

### Appointments Table
```sql
CREATE TABLE appointments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,        -- Links to the user who owns this appointment
    title VARCHAR(100) NOT NULL,     -- Appointment title
    doctor VARCHAR(100),             -- Doctor name
    location VARCHAR(100),           -- Where the appointment is
    notes TEXT,                      -- Any additional information
    appointment_date DATETIME NOT NULL, -- When the appointment is scheduled
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- When this record was created
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

### Medications Table
```sql
CREATE TABLE medications (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,           -- Links to the user who owns this medication
    name VARCHAR(100) NOT NULL,         -- Medication name
    dosage VARCHAR(50) NOT NULL,        -- How much to take (e.g., "100mg")
    frequency VARCHAR(100) NOT NULL,    -- How often to take it
    start_date DATE NOT NULL,           -- When to start taking this
    end_date DATE,                      -- When to stop taking this (optional)
    instructions TEXT,                  -- Any additional instructions
    remaining INT(11),                  -- Pills remaining (optional)
    refill_reminder BOOLEAN DEFAULT 0,  -- Whether to remind about refills
    refill_reminder_threshold INT(5) DEFAULT 5, -- At what count to show reminder
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- When this record was created
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

### Medication Logs Table
```sql
CREATE TABLE medication_logs (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    medication_id INT(11) NOT NULL,      -- Links to the medication taken
    user_id INT(11) NOT NULL,            -- Links to the user who took it
    taken_at DATETIME NOT NULL,          -- When it was taken
    notes TEXT,                          -- Any notes about taking it
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- When this log was created
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

### Core Files
- `config.php`: Sets up database connection and creates tables if needed
- `auth.php`: Handles all authentication functions (login, register, logout)

### Main Processing Functions

#### Authentication (auth.php)
- Login processing: Validates credentials and starts user session
- Registration: Validates and creates new user accounts
- Logout: Destroys user session

#### Appointments (appointments.php)
- Add appointment: Adds a new appointment record
- Update appointment: Modifies an existing appointment
- Delete appointment: Removes an appointment
- Verify ownership: Security check to ensure users only access their own data

#### Medications (medications.php)
- Add medication: Adds a new medication record
- Update medication: Modifies an existing medication
- Delete medication: Removes a medication and related logs
- Log dose: Records when medication is taken
- Update remaining: Updates pill count
- Verify ownership: Reusable function to check medication belongs to current user

### Helper Functions
- `verifyMedicationOwnership()`: Ensures users can only access their own medications
- `formatDate()`: Formats date for display (app.js)
- `setActiveNavLink()`: Highlights active navigation link (app.js)
- `count()`: Counts elements in an array
- `empty()`: Determines whether a variable is empty
- `isset()`: Determines if a variable is set and is not NULL

## Security Considerations

### Input Validation
- All user inputs are validated server-side before processing
- Required fields are checked to prevent empty submissions
- Email format validation for registration

### SQL Injection Prevention
- Prepared statements used for all database queries
- Parameter binding to separate SQL code from user input
- No direct inclusion of user input in SQL queries

### Authentication & Authorization
- Passwords are securely hashed using PHP's `password_hash()` function
- Password verification using `password_verify()`
- Session management to maintain login state
- Ownership verification to ensure users only access their own data

### Output Sanitization
- Data is sanitized before display with `htmlspecialchars()`
- Prevents XSS (Cross-Site Scripting) attacks

## Future Enhancements

### Possible Features
- Email notifications for upcoming appointments
- Improved medication schedule visualization
- Data export functionality (PDF, CSV)
- Medication interaction warnings
- Doctor/pharmacy contact management
- Health metrics tracking (weight, blood pressure, etc.)
- Calendar integration with Google/Apple calendars
- Prescription refill tracking and reminders

### Technical Improvements
- AJAX for smoother user experience without page reloads
- Responsive design improvements for mobile devices
- Improved data visualization with charts
- Accessibility enhancements
- More detailed error handling and user feedback
- Dark mode option for the UI
- Multi-language support
