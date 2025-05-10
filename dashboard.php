<?php
/**
 * DASHBOARD PAGE
 * This is the main page users see after logging in.
 * It shows appointment and medication summaries.
 */

// Include our database connection
require_once 'config.php';

// ===== SECURITY CHECK =====
// Make sure the user is logged in, or send them to login page
// The session variables are created when the user logs in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    // Not logged in! Redirect to login page
    header("Location: index.php");
    // Stop the script - don't show the dashboard to non-logged in users
    exit;
}

// ===== GET USER DATA =====
// Store the user ID in a variable to make our code cleaner
$user_id = $_SESSION["id"];

// ===== 1. APPOINTMENTS DATA =====
// Get the user's 5 most recent appointments
echo "<!-- Getting appointment data -->";

// SQL to get the 5 most recent appointments
$appointments_sql = "SELECT * FROM appointments 
                     WHERE user_id = ? 
                     ORDER BY appointment_date ASC 
                     LIMIT 5";

// Prepare the query - this makes it secure from SQL injection
$appointments_stmt = $conn->prepare($appointments_sql);

// Bind the user's ID to the ? in our SQL (i means integer type)
$appointments_stmt->bind_param("i", $user_id);

// Run the query
$appointments_stmt->execute();

// Get the results
$appointments_result = $appointments_stmt->get_result();

// Count how many appointments we found
$appointments_count = $appointments_result->num_rows;


// ===== 2. UPCOMING APPOINTMENTS COUNT =====
// Count how many future appointments the user has
echo "<!-- Counting upcoming appointments -->";

// SQL to count appointments in the future (after NOW)
$upcoming_sql = "SELECT COUNT(*) as count 
                 FROM appointments 
                 WHERE user_id = ? 
                 AND appointment_date >= NOW()";

// Prepare, bind, and execute like before
$upcoming_stmt = $conn->prepare($upcoming_sql);
$upcoming_stmt->bind_param("i", $user_id);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();

// Get the count value
$upcoming_appointments = $upcoming_result->fetch_assoc()['count'];


// ===== 3. ACTIVE MEDICATIONS COUNT =====
// Count the user's current medications (not expired)
echo "<!-- Counting active medications -->";

// SQL to count active medications (no end date or end date in future)
$active_meds_sql = "SELECT COUNT(*) as count 
                    FROM medications 
                    WHERE user_id = ? 
                    AND (end_date IS NULL OR end_date >= CURDATE())";

$active_meds_stmt = $conn->prepare($active_meds_sql);
$active_meds_stmt->bind_param("i", $user_id);
$active_meds_stmt->execute();
$active_meds_result = $active_meds_stmt->get_result();
$active_medications = $active_meds_result->fetch_assoc()['count'];


// ===== 4. DOSES TAKEN TODAY =====
// Count how many medication doses logged today
echo "<!-- Counting doses taken today -->";

// SQL to count medication logs for today
$doses_today_sql = "SELECT COUNT(*) as count 
                    FROM medication_logs 
                    WHERE user_id = ? 
                    AND DATE(taken_at) = CURDATE()";

$doses_today_stmt = $conn->prepare($doses_today_sql);
$doses_today_stmt->bind_param("i", $user_id);
$doses_today_stmt->execute();
$doses_today_result = $doses_today_stmt->get_result();
$doses_today = $doses_today_result->fetch_assoc()['count'];


// ===== 5. MEDICATIONS NEEDING REFILL =====
// Count medications that are running low
echo "<!-- Counting medications needing refill -->";

// SQL to find medications running low (below the reminder threshold)
$refill_sql = "SELECT COUNT(*) as count 
              FROM medications 
              WHERE user_id = ? 
              AND refill_reminder = 1 
              AND remaining <= refill_reminder_threshold 
              AND (end_date IS NULL OR end_date >= CURDATE())";

$refill_stmt = $conn->prepare($refill_sql);
$refill_stmt->bind_param("i", $user_id);
$refill_stmt->execute();
$refill_result = $refill_stmt->get_result();
$refill_needed = $refill_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="ar" dir="<?php echo get_direction(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app_name'); ?> - <?php echo __('dashboard'); ?></title>
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
        .card:hover {
            transform: translateY(-5px);
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
        /* RTL support for Arabic */
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
                        <a class="nav-link active" href="dashboard.php"><?php echo __('dashboard'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php"><?php echo __('appointments'); ?></a>
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
        <div class="welcome-section">
            <div class="d-flex justify-content-between align-items-center">
                <h1><?php echo __('welcome'); ?>, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
                <p class="mb-0"><i class="far fa-calendar-alt me-2"></i><?php 
                    // تحويل التاريخ من النمط الإنجليزي إلى العربي
                    $english_days = array('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
                    $arabic_days = array('السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة');
                    $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                    $arabic_months = array('يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر');
                    
                    $day = date('l');
                    $day_number = date('j');
                    $month = date('F');
                    $year = date('Y');
                    
                    $day_ar = str_replace($english_days, $arabic_days, $day);
                    $month_ar = str_replace($english_months, $arabic_months, $month);
                    
                    echo $day_ar . '، ' . $day_number . ' ' . $month_ar . ' ' . $year;
                ?></p>
            </div>
            <div class="row mt-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0"><?php echo __('upcoming_appointments'); ?></h5>
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                            <h2 class="display-4 mb-0"><?php echo $upcoming_appointments; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0"><?php echo __('active_medications'); ?></h5>
                                <i class="fas fa-pills fa-2x"></i>
                            </div>
                            <h2 class="display-4 mb-0"><?php echo $active_medications; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0"><?php echo __('medication_doses'); ?></h5>
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h2 class="display-4 mb-0"><?php echo $doses_today; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0"><?php echo __('refill_needed'); ?></h5>
                                <i class="fas fa-prescription-bottle fa-2x"></i>
                            </div>
                            <h2 class="display-4 mb-0"><?php echo $refill_needed; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Appointments Card -->
            <div class="col-md-12 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #205e7a;">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i><?php echo __('upcoming_appointments'); ?></h5>
                        <a href="appointments.php" class="btn btn-sm btn-light"><?php echo __('view_all'); ?></a>
                    </div>
                    <div class="card-body">
                        <?php if ($appointments_count > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('appointment_title'); ?></th>
                                            <th><?php echo __('doctor'); ?></th>
                                            <th><?php echo __('date_time'); ?></th>
                                            <th><?php echo __('location'); ?></th>
                                            <th><?php echo __('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $appointments_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                <td><?php echo htmlspecialchars($row['doctor']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        <?php 
                                                            $appointment_date = strtotime($row['appointment_date']);
                                                            $day_number = date('j', $appointment_date);
                                                            $month_name = date('M', $appointment_date);
                                                            $year = date('Y', $appointment_date);
                                                            
                                                            // تحويل الشهر للعربية
                                                            $english_short_months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
                                                            $arabic_short_months = array('يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر');
                                                            $month_name_ar = str_replace($english_short_months, $arabic_short_months, $month_name);
                                                            
                                                            echo $day_number . ' ' . $month_name_ar . ' ' . $year;
                                                        ?>
                                                    </span>
                                                    <span class="badge bg-secondary">
                                                        <i class="far fa-clock me-1"></i>
                                                        <?php 
                                                            $time = date('g:i', $appointment_date);
                                                            $am_pm = date('A', $appointment_date);
                                                            $am_pm_ar = $am_pm == 'AM' ? 'ص' : 'م';
                                                            echo $time . ' ' . $am_pm_ar;
                                                        ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                <td>
                                                    <a href="appointments.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center my-4 py-3">
                                <i class="far fa-calendar-times fs-1 text-muted mb-3"></i>
                                <p class="text-muted mb-0"><?php echo __('no_appointments_yet'); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="appointments.php?add=1" class="btn btn-info text-white w-100">
                                <i class="fas fa-plus me-1"></i><?php echo __('add_appointment'); ?>
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
                    <div class="card-header text-white" style="background-color: #205275;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-pills me-2"></i><?php echo __('my_medications'); ?></h5>
                            <a href="medications.php" class="btn btn-sm btn-light"><?php echo __('manage_all'); ?></a>
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
                                                <span class="badge bg-warning" title="<?php echo __('refill_needed_soon'); ?>"><i class="fas fa-exclamation-triangle"></i></span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small">
                                                <i class="fas fa-tablets text-primary me-1"></i> <?php echo htmlspecialchars($medication["dosage"]); ?><br>
                                                <i class="fas fa-clock text-primary me-1"></i> <?php echo htmlspecialchars($medication["frequency"]); ?>
                                                <?php if (!empty($medication["remaining"])): ?><br>
                                                <i class="fas fa-prescription-bottle text-primary me-1"></i> <?php echo $medication["remaining"]; ?> <?php echo __('remaining'); ?>
                                                <?php endif; ?>
                                            </p>
                                            <form action="medications.php" method="post" class="d-grid gap-2">
                                                <input type="hidden" name="action" value="quick_log">
                                                <input type="hidden" name="medication_id" value="<?php echo $medication["id"]; ?>">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check me-1"></i><?php echo __('log_dose'); ?>
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
                                <p class="text-muted"><?php echo __('no_medications_yet'); ?></p>
                                <a href="medications.php?add=1" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i><?php echo __('add_medication'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
