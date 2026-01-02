<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user = getCurrentUser();
$user_role = $current_user ? $current_user['role'] : null;
$notification_count = 0;

if (isLoggedIn()) {
    $notification_count = getUnreadNotificationCount($current_user['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container">
            <a class="navbar-brand text-success fw-bold" href="index.php">
                <i class="fas fa-leaf"></i> FoodSave
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        
                        <?php if ($user_role === 'donor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="donations_index.php">
                                    <i class="fas fa-list"></i> My Donations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="create.php">
                                    <i class="fas fa-plus-circle"></i> New Donation
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($user_role === 'receiver' || $user_role === 'volunteer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="donations_index.php">
                                    <i class="fas fa-search"></i> Find Donations
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($user_role === 'receiver'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="requests.php">
                                    <i class="fas fa-clipboard-list"></i> My Requests
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($user_role === 'volunteer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="requests.php">
                                    <i class="fas fa-tasks"></i> Available Requests
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($user_role === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">
                                    <i class="fas fa-user-shield"></i> Admin Panel
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.php">
                                <i class="fas fa-info-circle"></i> About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">
                                <i class="fas fa-envelope"></i> Contact
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if ($notification_count > 0): ?>
                            <li class="nav-item">
                                <a class="nav-link position-relative" href="notifications.php">
                                    <i class="fas fa-bell"></i>
                                    <span class="badge bg-danger notification-badge"><?php echo $notification_count; ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($current_user['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-success btn-sm" href="register.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <?php
    // Display flash messages
    $flash = getFlash();
    if ($flash):
    ?>
        <div class="container">
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
