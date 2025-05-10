<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Health - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <h1 class="display-4 fw-bold text-primary"><i class="fas fa-heartbeat me-2"></i>My Health</h1>
                    <p class="lead text-muted">Manage your health information in one place</p>
                </div>
                <div class="card shadow">
                    <div class="card-header text-white text-center" style="background: linear-gradient(to right, var(--primary), var(--secondary))">
                        <h3 class="mb-0">Welcome Back</h3>
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
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                                    <i class="fas fa-user-plus me-2"></i>Register
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content pt-3" id="authTabsContent">
                            <!-- Login Form -->
                            <div class="tab-pane fade show active" id="login" role="tabpanel">
                                <form action="includes/login_process.php" method="post" class="py-3">
                                    <div class="mb-4">
                                        <label for="login-username" class="form-label">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="login-username" name="username" placeholder="Enter your username" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="login-password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="login-password" name="password" placeholder="Enter your password" required>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary py-2">Sign In</button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Register Form -->
                            <div class="tab-pane fade" id="register" role="tabpanel">
                                <form action="includes/register_process.php" method="post" class="py-3">
                                    <div class="mb-3">
                                        <label for="register-username" class="form-label">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="register-username" name="username" placeholder="Choose a username" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="register-email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="register-email" name="email" placeholder="Enter your email" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="register-password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="register-password" name="password" placeholder="Create a password" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="register-confirm-password" class="form-label">Confirm Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-check-circle"></i></span>
                                            <input type="password" class="form-control" id="register-confirm-password" name="confirm_password" placeholder="Confirm your password" required>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary py-2">Create Account</button>
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
