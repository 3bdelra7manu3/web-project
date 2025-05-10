<?php
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

// Get user's reminders
$reminders_sql = "SELECT * FROM reminders WHERE user_id = ? ORDER BY reminder_date ASC LIMIT 5";
$reminders_stmt = $conn->prepare($reminders_sql);
$reminders_stmt->bind_param("i", $_SESSION["id"]);
$reminders_stmt->execute();
$reminders_result = $reminders_stmt->get_result();
$reminders_count = $reminders_result->num_rows;

// Count upcoming reminders
$upcoming_reminders_sql = "SELECT COUNT(*) as count FROM reminders WHERE user_id = ? AND reminder_date >= NOW()";
$upcoming_stmt = $conn->prepare($upcoming_reminders_sql);
$upcoming_stmt->bind_param("i", $_SESSION["id"]);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();
$upcoming_reminders = $upcoming_result->fetch_assoc()['count'];

// Get user's appointments - FIXED: removed the date restriction to show all appointments
$appointments_sql = "SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date ASC LIMIT 5";
$appointments_stmt = $conn->prepare($appointments_sql);
$appointments_stmt->bind_param("i", $_SESSION["id"]);
$appointments_stmt->execute();
$appointments_result = $appointments_stmt->get_result();
$appointments_count = $appointments_result->num_rows;

// Count upcoming appointments
$upcoming_appointments_sql = "SELECT COUNT(*) as count FROM appointments WHERE user_id = ? AND appointment_date >= NOW()";
$upcoming_appt_stmt = $conn->prepare($upcoming_appointments_sql);
$upcoming_appt_stmt->bind_param("i", $_SESSION["id"]);
$upcoming_appt_stmt->execute();
$upcoming_appt_result = $upcoming_appt_stmt->get_result();
$upcoming_appointments = $upcoming_appt_result->fetch_assoc()['count'];

// Get active medications count
$active_meds_sql = "SELECT COUNT(*) as count FROM medications WHERE user_id = ? AND (end_date IS NULL OR end_date >= CURDATE())";
$active_meds_stmt = $conn->prepare($active_meds_sql);
$active_meds_stmt->bind_param("i", $_SESSION["id"]);
$active_meds_stmt->execute();
$active_meds_result = $active_meds_stmt->get_result();
$active_medications = $active_meds_result->fetch_assoc()['count'];

// Get doses taken today
$doses_today_sql = "SELECT COUNT(*) as count FROM medication_logs WHERE user_id = ? AND DATE(taken_at) = CURDATE()";
$doses_today_stmt = $conn->prepare($doses_today_sql);
$doses_today_stmt->bind_param("i", $_SESSION["id"]);
$doses_today_stmt->execute();
$doses_today_result = $doses_today_stmt->get_result();
$doses_today = $doses_today_result->fetch_assoc()['count'];

// Get medications needing refill
$refill_sql = "SELECT COUNT(*) as count FROM medications WHERE user_id = ? AND refill_reminder = 1 AND remaining <= refill_reminder_threshold AND (end_date IS NULL OR end_date >= CURDATE())";
$refill_stmt = $conn->prepare($refill_sql);
$refill_stmt->bind_param("i", $_SESSION["id"]);
$refill_stmt->execute();
$refill_result = $refill_stmt->get_result();
$refill_needed = $refill_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Health - Dashboard</title>
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reminders.php">Reminders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medications.php">Medications</a>
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
        <div class="welcome-section">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
                <p class="mb-0"><i class="far fa-calendar-alt me-2"></i><?php echo date('l, F j, Y'); ?></p>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon"><i class="fas fa-bell"></i></div>
                    <h3><?php echo $upcoming_reminders; ?></h3>
                    <p>Upcoming Reminders</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon"><i class="fas fa-user-md"></i></div>
                    <h3><?php echo $upcoming_appointments; ?></h3>
                    <p>Upcoming Appointments</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon"><i class="fas fa-pills"></i></div>
                    <h3><?php echo $active_medications; ?></h3>
                    <p>Active Medications</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <h3><?php echo $doses_today; ?></h3>
                    <p>Doses Taken Today</p>
                </div>
            </div>
            <?php if ($refill_needed > 0): ?>
            <div class="col-md-12">
                <div class="alert alert-warning d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Refill Alert:</strong> <?php echo $refill_needed; ?> medication<?php echo $refill_needed > 1 ? 's' : ''; ?> need<?php echo $refill_needed == 1 ? 's' : ''; ?> refill soon. 
                        <a href="medications.php" class="alert-link ms-2">View medications</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="row">
            <!-- Upcoming Reminders Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Reminders</h5>
                        <a href="reminders.php" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($reminders_result->num_rows > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php while ($reminder = $reminders_result->fetch_assoc()): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($reminder["title"]); ?></h6>
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-alt me-1"></i>
                                                    <?php echo date("M d, Y - h:i A", strtotime($reminder["reminder_date"])); ?>
                                                </small>
                                                <?php if (strtotime($reminder["reminder_date"]) < time()): ?>
                                                    <span class="badge bg-secondary ms-2">Past</span>
                                                <?php elseif (strtotime($reminder["reminder_date"]) < strtotime('+1 day')): ?>
                                                    <span class="badge bg-warning ms-2">Today</span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="reminders.php?edit=<?php echo $reminder["id"]; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center my-4 py-3">
                                <i class="far fa-bell-slash fs-1 text-muted mb-3"></i>
                                <p class="text-muted mb-0">No reminders yet</p>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="reminders.php?add=1" class="btn btn-success w-100">
                                <i class="fas fa-plus me-1"></i>Add New Reminder
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Appointments Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Appointments</h5>
                        <a href="appointments.php" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($appointments_result->num_rows > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($appointment["title"]); ?></h6>
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="me-3">
                                                        <i class="far fa-calendar-alt me-1 text-primary"></i>
                                                        <?php echo date("M d, Y", strtotime($appointment["appointment_date"])); ?>
                                                    </span>
                                                    <span>
                                                        <i class="far fa-clock me-1 text-primary"></i>
                                                        <?php echo date("h:i A", strtotime($appointment["appointment_date"])); ?>
                                                    </span>
                                                    <?php if (strtotime($appointment["appointment_date"]) < time()): ?>
                                                        <span class="badge bg-secondary ms-2">Past</span>
                                                    <?php elseif (strtotime($appointment["appointment_date"]) < strtotime('+1 day')): ?>
                                                        <span class="badge bg-warning ms-2">Today</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex flex-wrap">
                                                    <?php if (!empty($appointment["doctor"])): ?>
                                                        <small class="me-3 text-muted">
                                                            <i class="fas fa-user-md me-1"></i>
                                                            <?php echo htmlspecialchars($appointment["doctor"]); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($appointment["location"])): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?php echo htmlspecialchars($appointment["location"]); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <a href="appointments.php?edit=<?php echo $appointment["id"]; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center my-4 py-3">
                                <i class="far fa-calendar-times fs-1 text-muted mb-3"></i>
                                <p class="text-muted mb-0">No appointments yet</p>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="appointments.php?add=1" class="btn btn-info text-white w-100">
                                <i class="fas fa-plus me-1"></i>Add New Appointment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Medications Section -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow">
                    <div class="card-header" style="background: linear-gradient(to right, var(--primary), var(--secondary)); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-pills me-2"></i>My Medications</h5>
                            <a href="medications.php" class="btn btn-sm btn-light">Manage Medications</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get active medications
                        $med_sql = "SELECT * FROM medications WHERE user_id = ? AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY name ASC LIMIT 6";
                        $med_stmt = $conn->prepare($med_sql);
                        $med_stmt->bind_param("i", $_SESSION["id"]);
                        $med_stmt->execute();
                        $medications_result = $med_stmt->get_result();
                        
                        if ($medications_result->num_rows > 0):
                        ?>
                            <div class="row">
                                <?php while($medication = $medications_result->fetch_assoc()): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="card-title d-flex justify-content-between align-items-center">
                                                <span><?php echo htmlspecialchars($medication["name"]); ?></span>
                                                <?php if ($medication["refill_reminder"] && $medication["remaining"] <= $medication["refill_reminder_threshold"]): ?>
                                                <span class="badge bg-warning" title="Refill needed soon"><i class="fas fa-exclamation-triangle"></i></span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small">
                                                <i class="fas fa-tablets text-primary me-1"></i> <?php echo htmlspecialchars($medication["dosage"]); ?><br>
                                                <i class="fas fa-clock text-primary me-1"></i> <?php echo htmlspecialchars($medication["frequency"]); ?>
                                                <?php if (!empty($medication["remaining"])): ?><br>
                                                <i class="fas fa-prescription-bottle text-primary me-1"></i> <?php echo $medication["remaining"]; ?> remaining
                                                <?php endif; ?>
                                            </p>
                                            <form action="includes/medication_process.php" method="post" class="d-grid gap-2">
                                                <input type="hidden" name="action" value="quick_log">
                                                <input type="hidden" name="medication_id" value="<?php echo $medication["id"]; ?>">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check me-1"></i>Log Dose
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center my-4">
                                <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                                <p class="text-muted">You haven't added any medications yet.</p>
                                <a href="medications.php?add=1" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Add Your First Medication
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
