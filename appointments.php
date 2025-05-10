<?php
require_once 'includes/db_connect.php';

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Health - Appointments</title>
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
                        <a class="nav-link active" href="appointments.php">Appointments</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo (isset($_GET['add']) || $edit_mode) ? ($edit_mode ? 'Edit Appointment' : 'Add New Appointment') : 'My Appointments'; ?></h1>
            <?php if (!isset($_GET['add']) && !$edit_mode): ?>
                <a href="appointments.php?add=1" class="btn btn-info text-white">
                    <i class="fas fa-plus me-1"></i>Add New Appointment
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
                    <form action="includes/appointment_process.php" method="post">
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'add'; ?>">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($appointment['title']); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="doctor" class="form-label">Doctor</label>
                                <input type="text" class="form-control" id="doctor" name="doctor"
                                       value="<?php echo htmlspecialchars($appointment['doctor']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       value="<?php echo htmlspecialchars($appointment['location']); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($appointment['notes']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Appointment Date & Time</label>
                            <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required
                                   value="<?php echo $appointment['appointment_date']; ?>">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_mode ? 'Update Appointment' : 'Add Appointment'; ?>
                            </button>
                            <a href="appointments.php" class="btn btn-secondary">Cancel</a>
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
                                    <th>Title</th>
                                    <th>Doctor</th>
                                    <th>Location</th>
                                    <th>Date & Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $appointments_result->fetch_assoc()): ?>
                                    <tr class="<?php echo (strtotime($row['appointment_date']) < time()) ? 'table-secondary' : ''; ?>">
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['doctor']); ?></td>
                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                        <td>
                                            <?php echo date("M d, Y - h:i A", strtotime($row['appointment_date'])); ?>
                                            <?php if (strtotime($row['appointment_date']) < time()): ?>
                                                <span class="badge bg-secondary">Past</span>
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
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this appointment?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Delete confirmation
        function confirmDelete(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDeleteBtn').href = 'includes/appointment_process.php?action=delete&id=' + id;
            modal.show();
        }
    </script>
</body>
</html>
