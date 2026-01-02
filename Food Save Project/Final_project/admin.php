<?php
require_once 'database.php';
require_once 'functions.php';

requireLogin();
requireRole('admin');

$conn = getDBConnection();

// Get all users
$users = getAllUsers();

// Get statistics
$stats = getAdminStats();

// Define the 3 admin accounts
$admin_emails = ['irisha@foodsave.com', 'mahin@foodsave.com', 'priyanti@foodsave.com'];
$current_admin_email = $_SESSION['user']['email'];

// Check if current user is one of the authorized admins
$is_authorized_admin = in_array($current_admin_email, $admin_emails);

if (!$is_authorized_admin) {
    $_SESSION['error'] = 'Access denied. You are not authorized to access the admin panel.';
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h2 class="mb-4"><i class="fas fa-user-shield text-danger"></i> Admin Panel</h2>

        <!-- Admin Info -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle"></i> <strong>Logged in as:</strong> <?php echo htmlspecialchars($current_admin_email); ?>
        </div>

        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p class="text-muted mb-0">Total Users</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-box fa-2x text-success mb-2"></i>
                        <h3><?php echo $stats['available_donations']; ?></h3>
                        <p class="text-muted mb-0">Available Donations</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                        <h3><?php echo $stats['pending_requests']; ?></h3>
                        <p class="text-muted mb-0">Pending Requests</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-double fa-2x text-info mb-2"></i>
                        <h3><?php echo $stats['completed_deliveries']; ?></h3>
                        <p class="text-muted mb-0">Completed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authorized Admins Info -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Authorized Administrators (3)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php 
                    $admin_names = ['Irisha', 'Mahin', 'Priyanti'];
                    foreach($admin_emails as $index => $admin_email): 
                    ?>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-user-shield text-danger fa-2x me-3"></i>
                            <div>
                                <strong class="d-block"><?php echo $admin_names[$index]; ?></strong>
                                <small class="text-muted"><?php echo $admin_email; ?></small>
                                <?php if($admin_email === $current_admin_email): ?>
                                <span class="badge bg-success ms-2">YOU</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">System Users</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Organization</th>
                                <th>Status</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php
                                    $role_colors = [
                                        'admin' => 'danger',
                                        'donor' => 'primary',
                                        'receiver' => 'info',
                                        'volunteer' => 'warning'
                                    ];
                                    $color = $role_colors[$user['role']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user['organization'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
