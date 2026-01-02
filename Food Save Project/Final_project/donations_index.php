<?php
require_once('database.php');
require_once('functions.php');

requireLogin();

$current_user = getCurrentUser();
$role = $current_user['role'];

// Get donations based on role
if ($role === 'donor') {
    $donations = getAllDonations(['donor_id' => $current_user['user_id']]);
} else {
    $donations = getAvailableDonations();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <h2>
                    <i class="fas fa-box text-success"></i> 
                    <?php echo $role === 'donor' ? 'My Donations' : 'Available Donations'; ?>
                </h2>
            </div>
            <?php if ($role === 'donor'): ?>
                <div class="col-auto">
                    <a href="create.php" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> New Donation
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (count($donations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Food Name</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Expiry Date</th>
                                    <th>Location</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donation['food_name']); ?></td>
                                        <td><?php echo htmlspecialchars($donation['quantity'] . ' ' . $donation['unit']); ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = [
                                                'available' => 'success',
                                                'reserved' => 'warning',
                                                'collected' => 'info',
                                                'expired' => 'danger'
                                            ];
                                            $class = $badge_class[$donation['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $class; ?>">
                                                <?php echo ucfirst($donation['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($donation['expiry_date']) {
                                                $expiry = new DateTime($donation['expiry_date']);
                                                $now = new DateTime();
                                                $diff = $now->diff($expiry);
                                                $days = $diff->days;
                                                
                                                if ($expiry < $now) {
                                                    echo '<span class="text-danger">Expired</span>';
                                                } else {
                                                    echo date('M d, Y', strtotime($donation['expiry_date']));
                                                    echo ' <small class="text-muted">(' . $days . ' days left)</small>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">No expiry</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($donation['location'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></td>
                                        <td>
                                            <a href="view_donation.php?id=<?php echo $donation['donation_id']; ?>" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <?php if ($role === 'receiver' && $donation['status'] === 'available'): ?>
                                                <a href="request_donation.php?id=<?php echo $donation['donation_id']; ?>" 
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-hand-paper"></i> Request
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No donations found</h5>
                        <?php if ($role === 'donor'): ?>
                            <p>Create your first donation to get started!</p>
                            <a href="create.php" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Create Donation
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
