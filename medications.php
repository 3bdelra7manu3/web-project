<?php
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Health - Medications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">My Health</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reminders.php">Reminders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="medications.php">Medications</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION["username"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="includes/logout.php">Logout</a></li>
                        </ul>
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
                                <form action="includes/medication_process.php" method="post" class="d-flex">
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
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Log Medication</h5>
                        </div>
                        <div class="card-body">
                            <form action="includes/medication_process.php" method="post">
                                <input type="hidden" name="action" value="log_dose">
                                <input type="hidden" name="medication_id" value="<?php echo $medication['id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="taken_at" class="form-label">When did you take it?</label>
                                    <input type="datetime-local" class="form-control" id="taken_at" name="taken_at" 
                                           value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes (optional)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success py-2">
                                        <i class="fas fa-check me-1"></i>Log Dose
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
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent History</h5>
                </div>
                <div class="card-body">
                    <?php if (count($med_logs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
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
                <h1><?php echo $edit_mode ? 'Edit Medication' : 'Add New Medication'; ?></h1>
                <a href="medications.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Medications
                </a>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form action="includes/medication_process.php" method="post">
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'add'; ?>">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $medication['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Medication Name</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo htmlspecialchars($medication['name']); ?>" placeholder="e.g., Ibuprofen">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="dosage" class="form-label">Dosage</label>
                                <input type="text" class="form-control" id="dosage" name="dosage" required
                                       value="<?php echo htmlspecialchars($medication['dosage']); ?>" placeholder="e.g., 200mg">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="frequency" class="form-label">Frequency</label>
                            <input type="text" class="form-control" id="frequency" name="frequency" required
                                   value="<?php echo htmlspecialchars($medication['frequency']); ?>" placeholder="e.g., Twice daily with meals">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required
                                       value="<?php echo $medication['start_date']; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date (optional)</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?php echo $medication['end_date']; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="instructions" class="form-label">Instructions (optional)</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="3"
                                     placeholder="Any special instructions for taking this medication"><?php echo htmlspecialchars($medication['instructions']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="remaining" class="form-label">Remaining Pills (optional)</label>
                                <input type="number" class="form-control" id="remaining" name="remaining" min="0"
                                       value="<?php echo $medication['remaining']; ?>" placeholder="How many pills do you have left?">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="refill_reminder_threshold" class="form-label">Refill Reminder Threshold</label>
                                <input type="number" class="form-control" id="refill_reminder_threshold" name="refill_reminder_threshold" min="1"
                                       value="<?php echo $medication['refill_reminder_threshold']; ?>" placeholder="Remind when X pills remain">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="refill_reminder" name="refill_reminder" value="1"
                                           <?php echo $medication['refill_reminder'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="refill_reminder">
                                        Enable refill reminders
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_mode ? 'Update Medication' : 'Add Medication'; ?>
                            </button>
                            <a href="medications.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Medications List -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Medications</h1>
                <a href="medications.php?add=1" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add New Medication
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
                                    <form action="includes/medication_process.php" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="quick_log">
                                        <input type="hidden" name="medication_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check me-1"></i>Log Dose Now
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
                        <h4>No medications yet</h4>
                        <p class="mb-0">Start tracking your medications by clicking the "Add New Medication" button.</p>
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
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this medication? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Log Confirmation Modal -->
    <div class="modal fade" id="deleteLogModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this medication log entry?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteLogBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Delete confirmation
        function confirmDelete(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDeleteBtn').href = 'includes/medication_process.php?action=delete&id=' + id;
            modal.show();
        }
        
        // Delete log confirmation
        function confirmDeleteLog(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteLogModal'));
            document.getElementById('confirmDeleteLogBtn').href = 'includes/medication_process.php?action=delete_log&id=' + id + '&medication_id=<?php echo isset($_GET['view']) ? $_GET['view'] : ''; ?>';
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
