<?php
require_once('database.php');
require_once('functions.php');

// Get statistics for homepage
$conn = getDBConnection();

$stats = [
    'total_users' => 0,
    'total_donations' => 0,
    'total_requests' => 0,
    'active_volunteers' => 0
];

// Get user count
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
if ($result) {
    $stats['total_users'] = $result->fetch_assoc()['count'];
}

// Get donations count
$result = $conn->query("SELECT COUNT(*) as count FROM donations");
if ($result) {
    $stats['total_donations'] = $result->fetch_assoc()['count'];
}

// Get requests count
$result = $conn->query("SELECT COUNT(*) as count FROM requests");
if ($result) {
    $stats['total_requests'] = $result->fetch_assoc()['count'];
}

// Get active volunteers count
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'volunteer' AND is_active = 1");
if ($result) {
    $stats['active_volunteers'] = $result->fetch_assoc()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Food Waste Reduction System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include('header.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section text-center py-5">
        <div class="container">
            <h1 class="display-4 fw-bold text-success mb-3">
                <i class="fas fa-leaf"></i> FoodSave
            </h1>
            <p class="lead text-muted mb-4">
                Connect surplus food from restaurants, supermarkets, and events with people in need. 
                A complete college project demonstrating food waste reduction system.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-success btn-lg">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-success btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-outline-success btn-lg">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Simple 3-step process</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="display-4 text-success mb-3">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <h5 class="card-title">1. Donate Food</h5>
                            <p class="card-text">Restaurants, supermarkets, and individuals list surplus food.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="display-4 text-success mb-3">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <h5 class="card-title">2. Request Food</h5>
                            <p class="card-text">Charities and shelters request available food donations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="display-4 text-success mb-3">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h5 class="card-title">3. Deliver</h5>
                            <p class="card-text">Volunteers pick up and deliver food to those in need.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- User Roles -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Four different roles for different purposes</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-store fa-3x text-primary mb-3"></i>
                            <h5>Donors</h5>
                            <p class="small">Restaurants, supermarkets with surplus food.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-home fa-3x text-info mb-3"></i>
                            <h5>Receivers</h5>
                            <p class="small">Charities, shelters in need of food.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-hands-helping fa-3x text-warning mb-3"></i>
                            <h5>Volunteers</h5>
                            <p class="small">Individuals who help transport food.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-user-shield fa-3x text-danger mb-3"></i>
                            <h5>Admins</h5>
                            <p class="small">System administrators.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Showing sample data for demonstration</h2>
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x text-success mb-2"></i>
                            <h3 class="mb-0"><?php echo $stats['total_users']; ?></h3>
                            <p class="text-muted mb-0">Users Registered</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-hamburger fa-2x text-warning mb-2"></i>
                            <h3 class="mb-0"><?php echo $stats['total_donations']; ?></h3>
                            <p class="text-muted mb-0">Food Donations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-clipboard-list fa-2x text-info mb-2"></i>
                            <h3 class="mb-0"><?php echo $stats['total_requests']; ?></h3>
                            <p class="text-muted mb-0">Collection Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-user-check fa-2x text-primary mb-2"></i>
                            <h3 class="mb-0"><?php echo $stats['active_volunteers']; ?></h3>
                            <p class="text-muted mb-0">Active Volunteers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Login Credentials -->
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-4">Demo Login Credentials</h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Password</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-danger">Admin</span></td>
                                    <td>admin04@example.com</td>
                                    <td>admin123</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary">Donor</span></td>
                                    <td>donor01@example.com</td>
                                    <td>password123</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">Receiver</span></td>
                                    <td>receiver01@example.com</td>
                                    <td>password123</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Volunteer</span></td>
                                    <td>volunteer01@example.com</td>
                                    <td>password123</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 FoodSave - Food Waste Reduction System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</body>
</html>
