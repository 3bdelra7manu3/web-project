<?php
/**
 * APPOINTMENTS PAGE
 * This page allows users to view, add, edit, and delete appointments.
 */

// Connect to our database by including config.php
require_once 'config.php';

// تضمين ملف تكوين اللغة
require_once 'lang/config.php';

// ===== PROCESSING FORM SUBMISSIONS =====
// Check if this is a form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check what action the user wants to perform (add or update)
    if (isset($_POST["action"]) && ($_POST["action"] == "add" || $_POST["action"] == "update")) {
        echo "<!-- Processing appointment form -->";
        
        // ===== 1. GET ALL FORM DATA =====
        // Get all the information from the appointment form
        $title = $_POST["title"];                  // Appointment title
        $doctor = $_POST["doctor"];                // Doctor name
        $location = $_POST["location"];            // Appointment location
        $notes = $_POST["notes"];                  // Any notes about the appointment
        $appointment_date = $_POST["appointment_date"]; // When the appointment is scheduled
        $user_id = $_SESSION["id"];               // Which user this appointment belongs to
        
        // ===== 2. VALIDATE FORM DATA =====
        // Make sure required fields are filled in
        if ($title == "" || $appointment_date == "") {
            // Send user back to form with error message
            header("Location: appointments.php?error=Please fill in all required fields");
            exit;
        }
        
        // ===== 3. PROCESS BASED ON ACTION TYPE =====
        // Different processing for adding vs updating
        if ($_POST["action"] == "add") {
            echo "<!-- Adding a new appointment -->";
            
            // Create SQL for inserting a new appointment
            $sql = "INSERT INTO appointments (user_id, title, doctor, location, notes, appointment_date) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            // Prepare the statement for security
            $stmt = $conn->prepare($sql);
            
            // Bind parameters to the placeholders in our SQL
            // i = integer, s = string (for the user_id and other fields)
            $stmt->bind_param("isssss", $user_id, $title, $doctor, $location, $notes, $appointment_date);
            
            // Try to save the new appointment
            if ($stmt->execute()) {
                // It worked! Send user back with success message
                header("Location: appointments.php?success=Appointment added successfully");
                exit;
            } else {
                // Something went wrong
                header("Location: appointments.php?error=Something went wrong with the database");
                exit;
            }
        } 
        // If we're updating an existing appointment
        else if ($_POST["action"] == "update") {
            echo "<!-- Updating an existing appointment -->";
            
            // ===== 3.1 GET THE APPOINTMENT ID =====
            // We need to know which appointment to update
            $id = $_POST["id"];
            
            // ===== 3.2 SECURITY CHECK - VERIFY OWNERSHIP =====
            // IMPORTANT: Make sure this appointment belongs to the current user
            // This prevents users from modifying each other's appointments
            echo "<!-- Security check: verifying appointment ownership -->";
            
            // SQL to check if this appointment belongs to this user
            $check_sql = "SELECT id FROM appointments WHERE id = ? AND user_id = ?";
            
            // Prepare the statement
            $check_stmt = $conn->prepare($check_sql);
            
            // Bind parameters (i = integer)
            $check_stmt->bind_param("ii", $id, $user_id);
            
            // Run the security check
            $check_stmt->execute();
            
            // Get results
            $check_result = $check_stmt->get_result();
            
            // If no matching appointment found for this user
            if ($check_result->num_rows == 0) {
                // Security violation! Send them back with error
                header("Location: appointments.php?error=You don't have permission to edit this appointment");
                exit;
            }
            
            // ===== 3.3 UPDATE THE APPOINTMENT =====
            // All checks passed, now we can update the appointment
            echo "<!-- Performing appointment update -->";
            
            // SQL to update the appointment
            $sql = "UPDATE appointments 
                   SET title = ?, 
                       doctor = ?, 
                       location = ?, 
                       notes = ?, 
                       appointment_date = ? 
                   WHERE id = ? AND user_id = ?";
            
            // Prepare statement
            $stmt = $conn->prepare($sql);
            
            // Bind all parameters (s = string, i = integer)
            $stmt->bind_param("sssssii", 
                              $title, 
                              $doctor, 
                              $location, 
                              $notes, 
                              $appointment_date, 
                              $id, 
                              $user_id);
            
            // Try to update the appointment
            if ($stmt->execute()) {
                // Success! Send user back with success message
                header("Location: appointments.php?success=Appointment updated successfully");
                exit;
            } else {
                // Something went wrong
                header("Location: appointments.php?error=Something went wrong while updating");
                exit;
            }
        }
    }
}

// ===== HANDLING APPOINTMENT DELETION =====
// This happens when the user confirms deletion in the modal dialog
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    echo "<!-- Processing appointment deletion -->";
    
    // Get the ID of the appointment to delete
    $id = $_GET["id"];
    
    // Get the current user's ID for security checks
    $user_id = $_SESSION["id"];
    
    // ===== 1. SECURITY CHECK - VERIFY OWNERSHIP =====
    // IMPORTANT: Make sure this appointment belongs to this user
    // This prevents users from deleting each other's appointments
    echo "<!-- Security check: verifying appointment ownership before delete -->";
    
    // SQL to check if this appointment belongs to this user
    $check_sql = "SELECT id FROM appointments WHERE id = ? AND user_id = ?";
    
    // Prepare the statement
    $check_stmt = $conn->prepare($check_sql);
    
    // Bind parameters (i = integer)
    $check_stmt->bind_param("ii", $id, $user_id);
    
    // Run the security check
    $check_stmt->execute();
    
    // Get results
    $check_result = $check_stmt->get_result();
    
    // If no matching appointment found for this user
    if ($check_result->num_rows == 0) {
        // Security violation! Redirect with error message
        header("Location: appointments.php?error=You don't have permission to delete this appointment");
        exit;
    }
    
    // ===== 2. DELETE THE APPOINTMENT =====
    // Security check passed, now we can delete
    echo "<!-- Performing appointment deletion -->";
    
    // SQL to delete the appointment
    // We include user_id in WHERE clause as an extra security measure
    $sql = "DELETE FROM appointments WHERE id = ? AND user_id = ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters (i = integer)
    $stmt->bind_param("ii", $id, $user_id);
    
    // Try to delete the appointment
    if ($stmt->execute()) {
        // Success! Redirect with success message
        header("Location: appointments.php?success=Your appointment was deleted successfully");
        exit;
    } else {
        // Something went wrong
        header("Location: appointments.php?error=Something went wrong while deleting the appointment");
        exit;
    }
}

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

// Handle add or edit
$edit_mode = false;
$appointment = [
    "id" => "",
    "title" => "",
    "doctor" => "",
    "location" => "",
    "notes" => "",
    "appointment_date" => date("Y-m-d\TH:i")
];

// Edit appointment
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $appointment_id = $_GET['edit'];
    
    $sql = "SELECT * FROM appointments WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $appointment_id, $_SESSION["id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $appointment = $result->fetch_assoc();
        // Format date for datetime-local input
        $appointment["appointment_date"] = date("Y-m-d\TH:i", strtotime($appointment["appointment_date"]));
    } else {
        header("Location: appointments.php");
        exit;
    }
}

// Get all appointments for the user
$sql = "SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$appointments_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="<?php echo get_direction(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app_name'); ?> - <?php echo __('appointments'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2980b9;
            --success: #2ecc71;
            --info: #4dbfd9;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f5f5f5;
            --dark: #18232d;
            --bs-body-bg: #121212;
            --bs-body-color: #f8f9fa;
            --bs-card-bg: #242424;
            --bs-border-color: #444;
        }
        
        /* الوضع الداكن */
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            text-align: <?php echo get_align(); ?>;
        }
        
        .card {
            border-radius: 15px;
            overflow: hidden;
            border: none;
            transition: transform 0.3s;
            background-color: var(--bs-card-bg);
            border-color: var(--bs-border-color);
        }
        
        .card-header {
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            background-color: rgba(0, 0, 0, 0.2) !important;
            border-color: var(--bs-border-color);
        }
        
        .navbar {
            background-color: #1a1a1a !important;
        }
        
        .table {
            color: var(--bs-body-color);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .text-muted {
            color: #adb5bd !important;
        }
        
        .form-control, .input-group-text {
            background-color: #333;
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        
        .form-control:focus {
            background-color: #444;
            color: white;
        }
        
        /* دعم RTL للغة العربية */
        .me-2, .me-3 {
            margin-<?php echo get_align(); ?>: 0.5rem !important;
            margin-<?php echo get_opposite_align(); ?>: 0 !important;
        }
        
        .ms-auto {
            margin-<?php echo get_align(); ?>: auto !important;
            margin-<?php echo get_opposite_align(); ?>: 0 !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1a1a1a;">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><?php echo __('app_name'); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><?php echo __('dashboard'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="appointments.php"><?php echo __('appointments'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medications.php"><?php echo __('medications'); ?></a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="auth.php?action=logout">
                            <i class="fas fa-sign-out-alt me-2"></i><?php echo __('logout'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo (isset($_GET['add']) || $edit_mode) ? ($edit_mode ? __('edit_appointment') : __('add_appointment')) : __('my_appointments'); ?></h1>
            <?php if (!isset($_GET['add']) && !$edit_mode): ?>
                <a href="appointments.php?add=1" class="btn btn-info text-white">
                    <i class="fas fa-plus me-1"></i><?php echo __('add_appointment'); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['add']) || $edit_mode): ?>
            <!-- Add/Edit Appointment Form -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form action="appointments.php" method="post">
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'add'; ?>">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label"><?php echo __('appointment_title'); ?></label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($appointment['title']); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="doctor" class="form-label"><?php echo __('doctor'); ?></label>
                                <input type="text" class="form-control" id="doctor" name="doctor"
                                       value="<?php echo htmlspecialchars($appointment['doctor']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label"><?php echo __('location'); ?></label>
                                <input type="text" class="form-control" id="location" name="location"
                                       value="<?php echo htmlspecialchars($appointment['location']); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?php echo __('notes'); ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($appointment['notes']); ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="appointment_date" class="form-label"><?php echo __('date_time'); ?></label>
                            <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required
                                   value="<?php echo $appointment['appointment_date']; ?>">
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="appointments.php" class="btn btn-secondary"><?php echo __('cancel'); ?></a>
                            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? __('edit') : __('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Appointments List -->
            <?php if ($appointments_result->num_rows > 0): ?>
                <div class="card shadow">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo __('appointment_title'); ?></th>
                                    <th><?php echo __('doctor'); ?></th>
                                    <th><?php echo __('location'); ?></th>
                                    <th><?php echo __('date_time'); ?></th>
                                    <th><?php echo __('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $appointments_result->fetch_assoc()): ?>
                                    <tr class="<?php echo (strtotime($row['appointment_date']) < time()) ? 'table-secondary' : ''; ?>">
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['doctor']); ?></td>
                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                        <td>
                                            <?php 
                                                $appointment_date = strtotime($row['appointment_date']);
                                                $day_number = date('d', $appointment_date);
                                                $month_name = date('M', $appointment_date);
                                                $year = date('Y', $appointment_date);
                                                $time = date('h:i', $appointment_date);
                                                $am_pm = date('A', $appointment_date);
                                                
                                                // تحويل الشهر للعربية
                                                $english_short_months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
                                                $arabic_short_months = array('يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر');
                                                $month_name_ar = str_replace($english_short_months, $arabic_short_months, $month_name);
                                                
                                                // تحويل صباحًا/مساءً
                                                $am_pm_ar = $am_pm == 'AM' ? 'ص' : 'م';
                                                
                                                echo $day_number . ' ' . $month_name_ar . ' ' . $year . ' - ' . $time . ' ' . $am_pm_ar;
                                            ?>
                                            <?php if (strtotime($row['appointment_date']) < time()): ?>
                                                <span class="badge bg-secondary"><?php echo __('past'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="appointments.php?edit=<?php echo $row['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">You don't have any appointments yet. Click the "Add New Appointment" button to create one.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('confirm_delete_appointment'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo __('confirm_delete_appointment'); ?>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger"><?php echo __('delete'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Delete confirmation
        function confirmDelete(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDeleteBtn').href = 'appointments.php?action=delete&id=' + id;
            modal.show();
        }
    </script>
</body>
</html>
