<?php
// تضمين ملف التكوين
require_once 'lang/config.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="<?php echo get_direction(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app_name'); ?> - <?php echo __('login'); ?></title>
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
            text-align: <?php echo get_align(); ?>;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }
        
        .card {
            background-color: var(--bs-card-bg);
            border-color: var(--bs-border-color);
        }
        
        .card-header {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color);
        }
        
        .card-body {
            background-color: var(--bs-card-bg);
        }
        
        .nav-tabs {
            border-color: var(--bs-border-color);
        }
        
        .nav-tabs .nav-link {
            color: var(--bs-body-color);
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--bs-card-bg);
            border-color: var(--bs-border-color);
            color: var(--primary);
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
        
        .text-muted {
            color: #adb5bd !important;
        }
        
        /* دعم RTL للغة العربية */
        .me-2 {
            margin-<?php echo get_align(); ?>: 0.5rem !important;
            margin-<?php echo get_opposite_align(); ?>: 0 !important;
        }
        
        .custom-tabs .nav-link {
            text-align: <?php echo get_align(); ?>;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <h1 class="display-4 fw-bold text-primary"><i class="fas fa-heartbeat me-2"></i><?php echo __('app_name'); ?></h1>
                    <p class="lead text-muted"><?php echo __('health_management_slogan'); ?></p>
                </div>
                <div class="card shadow">
                    <div class="card-header text-white text-center" style="background: linear-gradient(to right, var(--primary), var(--secondary))">
                        <h3 class="mb-0"><?php echo __('welcome'); ?></h3>
                    </div>
                    <div class="card-body">
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
                        <ul class="nav nav-tabs custom-tabs" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                                    <i class="fas fa-sign-in-alt me-2"></i><?php echo __('login'); ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                                    <i class="fas fa-user-plus me-2"></i><?php echo __('register'); ?>
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content pt-3" id="authTabsContent">
                            <!-- Login Form -->
                            <div class="tab-pane fade show active" id="login" role="tabpanel">
                                <form action="auth.php" method="post" class="py-3">
                                    <input type="hidden" name="action" value="login">
                                    <div class="mb-3">
                                        <label for="username" class="form-label"><?php echo __('username'); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label"><?php echo __('password'); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-sign-in-alt me-2"></i><?php echo __('login'); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <!-- Register Form -->
                            <div class="tab-pane fade" id="register" role="tabpanel">
                                <form action="auth.php" method="post" class="py-3">
                                    <input type="hidden" name="action" value="register">
                                    <div class="mb-3">
                                        <label for="register-username" class="form-label"><?php echo __('username'); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="register-username" name="username" placeholder="Choose a username" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="register-email" class="form-label"><?php echo __('email'); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="register-email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="register-password" class="form-label"><?php echo __('password'); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="register-password" name="password" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="register-confirm-password" class="form-label"><?php echo __('confirm_password'); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-check-circle"></i></span>
                                            <input type="password" class="form-control" id="register-confirm-password" name="confirm_password" required>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary py-2"><?php echo __('register'); ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
