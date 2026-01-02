<?php
require_once 'database.php';
require_once 'functions.php';

requireLogin();
requireRole('volunteer');

$current_user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = intval($_POST['request_id']);
    
    if (assignRequest($request_id, $current_user['user_id'])) {
        setFlash('success', 'Request accepted successfully!');
        header('Location: dashboard.php');
        exit;
    } else {
        setFlash('danger', 'Failed to accept request. It may have been already assigned.');
        header('Location: dashboard.php');
        exit;
    }
}

// If GET request with request_id, show confirmation
if (isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    $request = getRequestById($request_id);
    
    if (!$request) {
        setFlash('danger', 'Request not found.');
        header('Location: dashboard.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept Request - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0"><i class="fas fa-hand-paper"></i> Accept Delivery Request</h4>
                    </div>
                    <div class="card-body">
                        <h5>Request Details</h5>
                        <table class="table">
                            <tr>
                                <th width="30%">Food Item:</th>
                                <td><?php echo htmlspecialchars($request['food_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Quantity:</th>
                                <td><?php echo htmlspecialchars($request['quantity']) . ' ' . htmlspecialchars($request['unit']); ?></td>
                            </tr>
                            <tr>
                                <th>Donor:</th>
                                <td><?php echo htmlspecialchars($request['donor_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Receiver:</th>
                                <td><?php echo htmlspecialchars($request['receiver_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Pickup Location:</th>
                                <td><?php echo htmlspecialchars($request['pickup_location'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Request Date:</th>
                                <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                            </tr>
                        </table>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            By accepting this request, you agree to pick up the food from the donor and deliver it to the receiver.
                        </div>
                        
                        <form method="POST" action="assign.php">
                            <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
                            
                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Confirm & Accept Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
} else {
    // No request_id provided, redirect to dashboard
    header('Location: dashboard.php');
    exit;
}
?>
