<?php
require_once 'database.php';
require_once 'functions.php';

requireLogin();

$current_user = getCurrentUser();
$user_id = $current_user['user_id'];
$role = $current_user['role'];

// Get role-specific data
$recent_activities = [];
$stats = [];

if ($role == 'donor') {
    $recent_donations = getAllDonations(['donor_id' => $user_id, 'limit' => 5]);
    $recent_activities = $recent_donations;
    $stats = getDonorStats($user_id);
    $available_donations = getAvailableDonations();
} elseif ($role == 'receiver') {
    $recent_requests = getAllRequests(['receiver_id' => $user_id, 'limit' => 5]);
    $recent_activities = $recent_requests;
    $stats = getReceiverStats($user_id);
    $available_donations = getAvailableDonations();
} elseif ($role == 'volunteer') {
    // ✅ FIXED: Get both assigned requests AND pending unassigned requests
    $my_requests = getAllRequests(['volunteer_id' => $user_id, 'limit' => 5]);
    $pending_requests = getPendingRequests(5);  // NEW: Get unassigned pending requests
    $recent_activities = $my_requests;
    $stats = getVolunteerStats($user_id);
} elseif ($role == 'admin') {
    $recent_donations = getAllDonations(['limit' => 5]);
    $recent_requests = getAllRequests(['limit' => 5]);
    $recent_activities = array_merge($recent_donations, $recent_requests);
    $stats = getAdminStats();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <h2>
                    <i class="fas fa-tachometer-alt text-success"></i> Dashboard - Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?>!
                </h2>
                <p class="text-muted">Role: <span class="badge bg-success"><?php echo ucfirst($role); ?></span></p>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <?php if ($role == 'donor'): ?>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-utensils fa-3x text-success mb-3"></i>
                            <h3><?php echo $stats['total_kg']; ?> kg</h3>
                            <p class="text-muted">Kilograms of Food Saved</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-box fa-3x text-warning mb-3"></i>
                            <h3><?php echo $stats['total_donations']; ?></h3>
                            <p class="text-muted">Total Donations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                            <h3><?php echo $stats['successful_donations']; ?></h3>
                            <p class="text-muted">Successful Donations</p>
                        </div>
                    </div>
                </div>
            <?php elseif ($role == 'receiver'): ?>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-clipboard-list fa-3x text-success mb-3"></i>
                            <h3><?php echo $stats['total_requests']; ?></h3>
                            <p class="text-muted">Total Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-check-double fa-3x text-info mb-3"></i>
                            <h3><?php echo $stats['collected_requests']; ?></h3>
                            <p class="text-muted">Collected Donations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-warning mb-3"></i>
                            <h3><?php echo ($stats['collected_requests'] * 5); ?></h3>
                            <p class="text-muted">Estimated Meals Provided</p>
                        </div>
                    </div>
                </div>
            <?php elseif ($role == 'volunteer'): ?>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-truck fa-3x text-success mb-3"></i>
                            <h3><?php echo $stats['total_deliveries']; ?></h3>
                            <p class="text-muted">Total Deliveries</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                            <h3><?php echo $stats['completed_deliveries']; ?></h3>
                            <p class="text-muted">Completed Deliveries</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                            <h3><?php echo ($stats['completed_deliveries'] * 2); ?> hrs</h3>
                            <p class="text-muted">Hours Volunteered</p>
                        </div>
                    </div>
                </div>
            <?php elseif ($role == 'admin'): ?>
                <div class="col-md-3">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-success mb-3"></i>
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p class="text-muted">Total Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-box fa-3x text-warning mb-3"></i>
                            <h3><?php echo $stats['available_donations']; ?></h3>
                            <p class="text-muted">Available Donations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-hourglass-half fa-3x text-info mb-3"></i>
                            <h3><?php echo $stats['pending_requests']; ?></h3>
                            <p class="text-muted">Pending Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center stat-card">
                        <div class="card-body">
                            <i class="fas fa-check-double fa-3x text-primary mb-3"></i>
                            <h3><?php echo $stats['completed_deliveries']; ?></h3>
                            <p class="text-muted">Completed</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- ✅ NEW: Available Requests for Volunteers -->
        <?php if ($role == 'volunteer' && count($pending_requests) > 0): ?>
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-tasks"></i> Available Requests (Need Volunteers)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Food Item</th>
                                        <th>Quantity</th>
                                        <th>Receiver</th>
                                        <th>Location</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['food_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['quantity']) . ' ' . htmlspecialchars($request['unit']); ?></td>
                                        <td><?php echo htmlspecialchars($request['receiver_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['location'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                        <td>
                                            <a href="assign.php?request_id=<?php echo $request['request_id']; ?>" 
                                               class="btn btn-sm btn-success">
                                                <i class="fas fa-hand-paper"></i> Accept
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Activities -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_activities) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Details</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_activities as $activity): ?>
                                    <tr>
                                        <td>
                                            <?php if (isset($activity['food_name']) && !isset($activity['donation_id'])): ?>
                                                <span class="badge bg-primary">Donation</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Request</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($activity['food_name'])): ?>
                                                <?php echo htmlspecialchars($activity['food_name']); ?>
                                                (<?php echo htmlspecialchars($activity['quantity']) . ' ' . htmlspecialchars($activity['unit']); ?>)
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $activity['status'] ?? 'N/A';
                                            $badge_class = [
                                                'available' => 'success',
                                                'reserved' => 'warning',
                                                'collected' => 'info',
                                                'expired' => 'danger',
                                                'pending' => 'warning',
                                                'assigned' => 'info'
                                            ];
                                            $class = $badge_class[$status] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $class; ?>"><?php echo ucfirst($status); ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $date = $activity['created_at'] ?? $activity['request_date'] ?? '';
                                            echo date('M d, Y', strtotime($date)); 
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            No recent activities
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2 d-md-flex">
                            <?php if ($role == 'donor'): ?>
                                <a href="create.php" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i> Create New Donation
                                </a>
                                <a href="donations_index.php" class="btn btn-outline-success">
                                    <i class="fas fa-list"></i> View All Donations
                                </a>
                            <?php elseif ($role == 'receiver'): ?>
                                <a href="donations_index.php" class="btn btn-success">
                                    <i class="fas fa-search"></i> Browse Available Donations
                                </a>
                                <a href="requests.php" class="btn btn-outline-success">
                                    <i class="fas fa-clipboard-list"></i> My Requests
                                </a>
                            <?php elseif ($role == 'volunteer'): ?>
                                <a href="requests.php" class="btn btn-success">
                                    <i class="fas fa-tasks"></i> View Available Requests
                                </a>
                                <a href="donations_index.php" class="btn btn-outline-success">
                                    <i class="fas fa-search"></i> Browse Donations
                                </a>
                            <?php elseif ($role == 'admin'): ?>
                                <a href="admin.php" class="btn btn-success">
                                    <i class="fas fa-user-shield"></i> Admin Panel
                                </a>
                                <a href="donations_index.php" class="btn btn-outline-success">
                                    <i class="fas fa-list"></i> All Donations
                                </a>
                                <a href="requests.php" class="btn btn-outline-success">
                                    <i class="fas fa-clipboard-list"></i> All Requests
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</body>
</html>
