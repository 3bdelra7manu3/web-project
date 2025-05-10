<?php
/* MEDICATIONS PAGE
   Handles adding, editing, viewing, and tracking medications */

// Connect to database
require_once 'config.php';

// تضمين ملف تكوين اللغة
require_once 'lang/config.php';

// Initialize user ID for security checks (used in multiple places)
$user_id = $_SESSION["id"] ?? 0;

// ===== PROCESS FORM SUBMISSIONS =====
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];
    
    // Common function to verify a medication belongs to current user
    function verifyMedicationOwnership($conn, $med_id, $user_id, $redirect_url="medications.php") {
        $check = $conn->prepare("SELECT id FROM medications WHERE id = ? AND user_id = ?");
        $check->bind_param("ii", $med_id, $user_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows == 0) {
            header("Location: $redirect_url?error=You don't have permission to access this medication");
            exit;
        }
        return true;
    }
    
    // ===== ADD NEW MEDICATION =====
    if ($action == "add") {
        // Get essential form data
        $name = $_POST["name"];
        $dosage = $_POST["dosage"];
        $frequency = $_POST["frequency"];
        $start_date = $_POST["start_date"];
        $end_date = !empty($_POST["end_date"]) ? $_POST["end_date"] : null;
        $instructions = $_POST["instructions"];
        $remaining = !empty($_POST["remaining"]) ? $_POST["remaining"] : null;
        $refill_reminder = isset($_POST["refill_reminder"]) ? 1 : 0;
        $refill_threshold = !empty($_POST["refill_reminder_threshold"]) ? $_POST["refill_reminder_threshold"] : 5;
        
        // Validate required fields
        if (empty($name) || empty($dosage) || empty($frequency) || empty($start_date)) {
            header("Location: medications.php?add=1&error=" . urlencode(__('fill_all_fields')));
            exit;
        }
        
        // Insert medication into database
        $sql = "INSERT INTO medications (user_id, name, dosage, frequency, start_date, end_date, 
               instructions, remaining, refill_reminder, refill_reminder_threshold) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
               
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssiis", $user_id, $name, $dosage, $frequency, $start_date, 
                          $end_date, $instructions, $remaining, $refill_reminder, $refill_threshold);
        
        // Redirect based on success/failure
        if ($stmt->execute()) {
            header("Location: medications.php?success=" . urlencode(__('medication_added')));
        } else {
            header("Location: medications.php?add=1&error=" . urlencode(__('could_not_save_medication')));
        }
        exit;
    }
    // ===== UPDATE MEDICATION =====
    else if ($action == "update") {
        // Get medication ID and data from form
        $id = $_POST["id"];
        $name = $_POST["name"];
        $dosage = $_POST["dosage"];
        $frequency = $_POST["frequency"];
        $start_date = $_POST["start_date"];
        $end_date = !empty($_POST["end_date"]) ? $_POST["end_date"] : null;
        $instructions = $_POST["instructions"];
        $remaining = !empty($_POST["remaining"]) ? $_POST["remaining"] : null;
        $refill_reminder = isset($_POST["refill_reminder"]) ? 1 : 0;
        $refill_threshold = !empty($_POST["refill_reminder_threshold"]) ? $_POST["refill_reminder_threshold"] : 5;
        
        // Validate required fields
        if (empty($name) || empty($dosage) || empty($frequency) || empty($start_date)) {
            header("Location: medications.php?edit=$id&error=" . urlencode(__('fill_all_fields')));
            exit;
        }
        
        // Verify this medication belongs to current user
        verifyMedicationOwnership($conn, $id, $user_id, "medications.php");
        
        // Update medication in database
        $sql = "UPDATE medications 
               SET name = ?, dosage = ?, frequency = ?, start_date = ?, end_date = ?,
               instructions = ?, remaining = ?, refill_reminder = ?, refill_reminder_threshold = ? 
               WHERE id = ? AND user_id = ?";
               
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssiisii", 
                         $name, $dosage, $frequency, $start_date, $end_date, 
                         $instructions, $remaining, $refill_reminder, $refill_threshold, 
                         $id, $user_id);
        
        // Redirect based on success/failure
        if ($stmt->execute()) {
            header("Location: medications.php?success=" . urlencode(__('medication_updated')));
        } else {
            header("Location: medications.php?edit=$id&error=" . urlencode(__('could_not_update_medication')));
        }
        exit;
    }
    
    // ===== UPDATE MEDICATION SUPPLY (REMAINING PILLS) =====
    else if ($action == "update_remaining") {
        $id = $_POST["id"];
        $remaining = $_POST["remaining"];
        
        // Verify ownership
        verifyMedicationOwnership($conn, $id, $user_id);
        
        // Update the pill count
        $sql = "UPDATE medications SET remaining = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $remaining, $id, $user_id);
        
        if ($stmt->execute()) {
            header("Location: medications.php?view=$id&success=" . urlencode(__('medication_supply_updated')));
        } else {
            header("Location: medications.php?view=$id&error=" . urlencode(__('could_not_update_supply')));
        }
        exit;
    }
    
    // ===== LOG MEDICATION DOSE WITH DETAILS =====
    else if ($action == "log_dose" || $action == "quick_log") {
        $medication_id = $_POST["medication_id"];
        
        // Set time - current time for quick_log, or form value for regular log
        $taken_at = ($action == "quick_log") ? date("Y-m-d H:i:s") : $_POST["taken_at"];
        
        // Notes - 'Quick logged' for quick_log or form value for regular log
        $notes = ($action == "quick_log") ? "Quick logged" : ($_POST["notes"] ?? "");
        
        // Verify medication belongs to current user and get remaining count
        $check = $conn->prepare("SELECT id, remaining FROM medications WHERE id = ? AND user_id = ?");
        $check->bind_param("ii", $medication_id, $user_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows == 0) {
            header("Location: medications.php?error=You don't have permission to log this medication");
            exit;
        }
        
        // Get medication info for updating remaining count
        $medication = $result->fetch_assoc();
        
        // Add log entry to medication_logs table
        $log = $conn->prepare("INSERT INTO medication_logs (medication_id, user_id, taken_at, notes) VALUES (?, ?, ?, ?)");
        $log->bind_param("iiss", $medication_id, $user_id, $taken_at, $notes);
        
        if ($log->execute()) {
            // If we're tracking pill count, reduce it by 1
            if ($medication["remaining"] !== null) {
                // Make sure count doesn't go below 0
                $new_remaining = max(0, $medication["remaining"] - 1);
                
                // Update the medication's remaining count
                $update = $conn->prepare("UPDATE medications SET remaining = ? WHERE id = ?");
                $update->bind_param("ii", $new_remaining, $medication_id);
                $update->execute();
            }
            
            // Redirect based on which type of log we did
            if ($action == "quick_log") {
                header("Location: medications.php?success=Dose logged successfully");
            } else {
                header("Location: medications.php?view=$medication_id&success=Dose logged successfully");
            }
        } else {
            // Handle error
            $error_url = ($action == "quick_log") ? "medications.php" : "medications.php?view=$medication_id";
            header("Location: $error_url&error=Could not log dose");
        }
        exit;
    } else {
        header("Location: medications.php?error=Something went wrong");
        exit;
    }
}

// ===== HANDLE MEDICATION DELETION =====
// When user clicks the delete button and confirms in the modal
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $id = $_GET["id"];
    
    // Use our helper function to verify ownership
    verifyMedicationOwnership($conn, $id, $user_id);
    
    // Database operations should be done in a specific order
    // First delete related logs (to prevent orphaned records)
    $conn->prepare("DELETE FROM medication_logs WHERE medication_id = ? AND user_id = ?")
         ->bind_param("ii", $id, $user_id)
         ->execute();
    
    // Then delete the medication itself
    $delete = $conn->prepare("DELETE FROM medications WHERE id = ? AND user_id = ?");
    $delete->bind_param("ii", $id, $user_id);
    
    // Redirect based on success/failure
    if ($delete->execute()) {
        header("Location: medications.php?success=Medication deleted successfully");
    } else {
        header("Location: medications.php?error=Could not delete medication");
    }
    exit;
}

// ===== SECURITY CHECK =====
// Make sure the user is logged in before showing any content
// This should be at the top of the file, but we have function definitions first
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Not logged in - redirect to login page
    header("Location: index.php");
    exit; // Stop execution
}

// Handle add or edit
$edit_mode = false;
$medication = [
    "id" => "",
    "name" => "",
    "dosage" => "",
    "frequency" => "",
    "start_date" => date("Y-m-d"),
    "end_date" => "",
    "instructions" => "",
    "remaining" => "",
    "refill_reminder" => 0,
    "refill_reminder_threshold" => 5
];

// Edit medication
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $medication_id = $_GET['edit'];
    
    $sql = "SELECT * FROM medications WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $medication_id, $_SESSION["id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $medication = $result->fetch_assoc();
    } else {
        header("Location: medications.php");
        exit;
    }
}

// Get all medications for the user
$sql = "SELECT * FROM medications WHERE user_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$medications_result = $stmt->get_result();

// Count active medications
$active_sql = "SELECT COUNT(*) as count FROM medications WHERE user_id = ? AND (end_date IS NULL OR end_date >= CURDATE())";
$active_stmt = $conn->prepare($active_sql);
$active_stmt->bind_param("i", $_SESSION["id"]);
$active_stmt->execute();
$active_result = $active_stmt->get_result();
$active_count = $active_result->fetch_assoc()['count'];

// Get medication logs if viewing details
$med_logs = [];
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $med_id = $_GET['view'];
    
    // Get medication details
    $sql = "SELECT * FROM medications WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $med_id, $_SESSION["id"]);
    $stmt->execute();
    $med_result = $stmt->get_result();
    
    if ($med_result->num_rows == 1) {
        $medication = $med_result->fetch_assoc();
        
        // Get medication logs
        $logs_sql = "SELECT * FROM medication_logs WHERE medication_id = ? AND user_id = ? ORDER BY taken_at DESC LIMIT 10";
        $logs_stmt = $conn->prepare($logs_sql);
        $logs_stmt->bind_param("ii", $med_id, $_SESSION["id"]);
        $logs_stmt->execute();
        $logs_result = $logs_stmt->get_result();
        
        while ($log = $logs_result->fetch_assoc()) {
            $med_logs[] = $log;
        }
    } else {
        header("Location: medications.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="<?php echo get_direction(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app_name'); ?> - <?php echo __('medications'); ?></title>
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
                        <a class="nav-link" href="appointments.php"><?php echo __('appointments'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="medications.php"><?php echo __('medications'); ?></a>
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
        <?php if (isset($_GET['view']) && !empty($_GET['view'])): ?>
            <!-- View Medication Details -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-pills me-2 text-primary"></i><?php echo htmlspecialchars($medication["name"]); ?></h1>
                <div>
                    <a href="medications.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Medications
                    </a>
                    <a href="medications.php?edit=<?php echo $medication['id']; ?>" class="btn btn-primary ms-2">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header" style="background: linear-gradient(to right, var(--primary), var(--secondary)); color: white;">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Medication Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Dosage:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($medication["dosage"]); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Frequency:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($medication["frequency"]); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Start Date:</div>
                                <div class="col-md-8"><?php echo date("M d, Y", strtotime($medication["start_date"])); ?></div>
                            </div>
                            <?php if (!empty($medication["end_date"])): ?>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">End Date:</div>
                                <div class="col-md-8"><?php echo date("M d, Y", strtotime($medication["end_date"])); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($medication["instructions"])): ?>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Instructions:</div>
                                <div class="col-md-8"><?php echo nl2br(htmlspecialchars($medication["instructions"])); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Remaining Pills:</div>
                                <div class="col-md-8">
                                    <?php if (!empty($medication["remaining"])): ?>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2"><?php echo htmlspecialchars($medication["remaining"]); ?></span>
                                            <?php if ($medication["remaining"] <= $medication["refill_reminder_threshold"] && $medication["refill_reminder"]): ?>
                                                <span class="badge bg-warning">Refill Soon</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Not tracked</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($medication["remaining"])): ?>
                            <div class="mt-3">
                                <form action="medications.php" method="post" class="d-flex">
                                    <input type="hidden" name="action" value="update_remaining">
                                    <input type="hidden" name="id" value="<?php echo $medication['id']; ?>">
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="remaining" value="<?php echo $medication['remaining']; ?>" min="0">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header" style="background: linear-gradient(to right, var(--success), var(--info)); color: white;">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i><?php echo __('log_dose'); ?></h5>
                        </div>
                        <div class="card-body">
                            <form action="medications.php" method="post">
                                <input type="hidden" name="action" value="log_dose">
                                <input type="hidden" name="medication_id" value="<?php echo $medication['id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="taken_at" class="form-label"><?php echo __('when_taken'); ?></label>
                                    <input type="datetime-local" class="form-control" id="taken_at" name="taken_at" 
                                           value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label"><?php echo __('notes'); ?></label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success py-2">
                                        <i class="fas fa-check me-1"></i><?php echo __('log_dose'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Log History -->
            <div class="card shadow mb-4">
                <div class="card-header" style="background: linear-gradient(to right, var(--info), var(--primary)); color: white;">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i><?php echo __('log_history'); ?></h5>
                </div>
                <div class="card-body">
                    <?php if (count($med_logs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo __('date_time'); ?></th>
                                        <th><?php echo __('notes'); ?></th>
                                        <th><?php echo __('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($med_logs as $log): ?>
                                    <tr>
                                        <td>
                                            <i class="far fa-clock me-1 text-primary"></i>
                                            <?php echo date("M d, Y - h:i A", strtotime($log["taken_at"])); ?>
                                        </td>
                                        <td><?php echo !empty($log["notes"]) ? htmlspecialchars($log["notes"]) : '<span class="text-muted">No notes</span>'; ?></td>
                                        <td>
                                            <a href="javascript:void(0);" onclick="confirmDeleteLog(<?php echo $log['id']; ?>)" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="medication_logs.php?medication_id=<?php echo $medication['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-history me-1"></i>View All History
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center my-4">
                            <i class="far fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No medication logs found yet.</p>
                            <p>Use the "Log Dose" form to track when you take this medication.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif (isset($_GET['add']) || $edit_mode): ?>
            <!-- Add/Edit Medication Form -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?php echo $edit_mode ? __('edit_medication') : __('add_medication'); ?></h1>
                <a href="medications.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i><?php echo __('back'); ?>
                </a>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form action="medications.php" method="post">
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'add'; ?>">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $medication['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label"><?php echo __('medication_name'); ?></label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo htmlspecialchars($medication['name']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="dosage" class="form-label"><?php echo __('dosage'); ?></label>
                                <input type="text" class="form-control" id="dosage" name="dosage" required
                                       value="<?php echo htmlspecialchars($medication['dosage']); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="frequency" class="form-label"><?php echo __('frequency'); ?></label>
                            <input type="text" class="form-control" id="frequency" name="frequency" required
                                   value="<?php echo htmlspecialchars($medication['frequency']); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label"><?php echo __('start_date'); ?></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required
                                       value="<?php echo $medication['start_date']; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label"><?php echo __('end_date'); ?></label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?php echo $medication['end_date']; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="instructions" class="form-label"><?php echo __('instructions'); ?></label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="3"><?php echo htmlspecialchars($medication['instructions']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="remaining" class="form-label"><?php echo __('remaining'); ?></label>
                                <input type="number" class="form-control" id="remaining" name="remaining" min="0"
                                       value="<?php echo $medication['remaining']; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_mode ? __('edit_medication') : __('add_medication'); ?>
                            </button>
                            <a href="medications.php" class="btn btn-secondary"><?php echo __('cancel'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Medications List -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?php echo __('my_medications'); ?></h1>
                <a href="medications.php?add=1" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i><?php echo __('add_medication'); ?>
                </a>
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
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="icon"><i class="fas fa-pills"></i></div>
                        <h3><?php echo $medications_result->num_rows; ?></h3>
                        <p>Total Medications</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="icon"><i class="fas fa-prescription-bottle-alt"></i></div>
                        <h3><?php echo $active_count; ?></h3>
                        <p>Active Medications</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="icon"><i class="fas fa-calendar-check"></i></div>
                        <h3 id="doses-today">-</h3>
                        <p>Doses Logged Today</p>
                    </div>
                </div>
            </div>
            
            <?php if ($medications_result->num_rows > 0): ?>
                <div class="row">
                    <?php while ($row = $medications_result->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow">
                                <div class="card-header d-flex justify-content-between align-items-center" 
                                    style="background: linear-gradient(to right, var(--primary), var(--secondary)); color: white;">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($row['name']); ?></h5>
                                    <?php if (empty($row['end_date']) || strtotime($row['end_date']) >= time()): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <i class="fas fa-tablets me-2 text-primary"></i>
                                        <strong>Dosage:</strong> <?php echo htmlspecialchars($row['dosage']); ?>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-clock me-2 text-primary"></i>
                                        <strong>Frequency:</strong> <?php echo htmlspecialchars($row['frequency']); ?>
                                    </div>
                                    <?php if (!empty($row['remaining'])): ?>
                                        <div class="mb-2">
                                            <i class="fas fa-prescription-bottle me-2 text-primary"></i>
                                            <strong>Remaining:</strong> 
                                            <span class="<?php echo ($row['refill_reminder'] && $row['remaining'] <= $row['refill_reminder_threshold']) ? 'text-warning fw-bold' : ''; ?>">
                                                <?php echo $row['remaining']; ?> pills
                                                <?php if ($row['refill_reminder'] && $row['remaining'] <= $row['refill_reminder_threshold']): ?>
                                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Refill needed soon"></i>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mt-3 d-flex justify-content-between">
                                        <a href="medications.php?view=<?php echo $row['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye me-1"></i>Details
                                        </a>
                                        <div>
                                            <a href="medications.php?edit=<?php echo $row['id']; ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn btn-outline-danger ms-1">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light d-flex justify-content-center">
                                    <form action="medications.php" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="quick_log">
                                        <input type="hidden" name="medication_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check me-1"></i><?php echo __('log_dose'); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <div class="text-center py-4">
                        <i class="fas fa-pills fa-3x mb-3 text-primary"></i>
                        <h4><?php echo __('no_medications_yet'); ?></h4>
                        <p><?php echo __('add_your_first_medication'); ?></p>
                        <a href="medications.php?add=1" class="btn btn-primary mt-2">
                            <i class="fas fa-plus me-1"></i><?php echo __('add_medication'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('confirm_delete_medication'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo __('confirm_delete_medication'); ?>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger"><?php echo __('delete'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Log Confirmation Modal -->
    <div class="modal fade" id="deleteLogModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('confirm_delete'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo __('confirm_delete_log'); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <a href="#" id="confirmDeleteLogBtn" class="btn btn-danger"><?php echo __('delete'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Delete confirmation
        function confirmDelete(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDeleteBtn').href = 'medications.php?action=delete&id=' + id;
            modal.show();
        }
        
        // Delete log confirmation
        function confirmDeleteLog(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteLogModal'));
            document.getElementById('confirmDeleteLogBtn').href = 'medications.php?action=delete_log&id=' + id + '&medication_id=<?php echo isset($_GET['view']) ? $_GET['view'] : ''; ?>';
            modal.show();
        }
        
        // Get doses taken today
        document.addEventListener('DOMContentLoaded', function() {
            // This would typically be done with AJAX
            // For now, just simulate it
            document.getElementById('doses-today').textContent = '<?php 
                // Calculate doses taken today
                $today_sql = "SELECT COUNT(*) as count FROM medication_logs WHERE user_id = ? AND DATE(taken_at) = CURDATE()";
                $today_stmt = $conn->prepare($today_sql);
                $today_stmt->bind_param("i", $_SESSION["id"]);
                $today_stmt->execute();
                $today_result = $today_stmt->get_result();
                echo $today_result->fetch_assoc()['count'];
            ?>';
        });
    </script>
</body>
</html>
