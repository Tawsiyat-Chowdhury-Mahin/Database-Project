<?php
require_once 'database.php';
require_once 'functions.php';
requireLogin();

$donation_id = $_GET['id'] ?? 0;
$donation = getDonationById($donation_id);

if (!$donation) {
    setFlash('danger', 'Donation not found.');
    header('Location: donations_index.php');
    exit;
}

$current_user = getCurrentUser();
$role = $current_user['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Donation - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-box"></i> Donation Details
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-success mb-3"><?php echo htmlspecialchars($donation['food_name']); ?></h5>
                                
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Quantity:</th>
                                        <td><?php echo htmlspecialchars($donation['quantity'] . ' ' . $donation['unit']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
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
                                    </tr>
                                    <tr>
                                        <th>Expiry Date:</th>
                                        <td>
                                            <?php 
                                            if ($donation['expiry_date']) {
                                                echo date('M d, Y', strtotime($donation['expiry_date']));
                                            } else {
                                                echo '<span class="text-muted">No expiry</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Location:</th>
                                        <td><?php echo htmlspecialchars($donation['location'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Created:</th>
                                        <td><?php echo date('M d, Y H:i', strtotime($donation['created_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Donor Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Name:</th>
                                        <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Organization:</th>
                                        <td><?php echo htmlspecialchars($donation['organization'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td><?php echo htmlspecialchars($donation['donor_phone'] ?? 'N/A'); ?></td>
                                    </tr>
                                </table>
                                
                                <?php if ($donation['description']): ?>
                                <h6 class="text-muted mb-2 mt-3">Description</h6>
                                <p><?php echo nl2br(htmlspecialchars($donation['description'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="donations_index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            
                            <?php if ($role == 'receiver' && $donation['status'] == 'available'): ?>
                                <a href="request_donation.php?id=<?php echo $donation['donation_id']; ?>" class="btn btn-success">
                                    <i class="fas fa-hand-paper"></i> Request This Donation
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
