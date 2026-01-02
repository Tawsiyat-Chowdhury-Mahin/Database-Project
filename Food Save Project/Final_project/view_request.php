<?php
require_once 'database.php';
require_once 'functions.php';
requireLogin();

$request_id = $_GET['id'] ?? 0;
$request = getRequestById($request_id);

if (!$request) {
    setFlash('danger', 'Request not found.');
    header('Location: requests.php');
    exit;
}

$current_user = getCurrentUser();
$role = $current_user['role'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'assign' && $role == 'volunteer' && $request['status'] == 'pending') {
        if (assignRequest($request_id, $current_user['user_id'])) {
            setFlash('success', 'You have been assigned to this delivery!');
            header('Location: view_request.php?id=' . $request_id);
            exit;
        }
    } elseif ($action == 'complete' && $role == 'volunteer' && $request['status'] == 'assigned') {
        if (completeRequest($request_id)) {
            setFlash('success', 'Delivery marked as complete!');
            header('Location: view_request.php?id=' . $request_id);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request - FoodSave</title>
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
                            <i class="fas fa-clipboard-list"></i> Request Details #<?php echo $request['request_id']; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-success mb-3">Donation Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Food Item:</th>
                                        <td><?php echo htmlspecialchars($request['food_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Quantity:</th>
                                        <td><?php echo htmlspecialchars($request['quantity'] . ' ' . $request['unit']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Pickup Location:</th>
                                        <td><?php echo htmlspecialchars($request['pickup_location']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <?php
                                            $badge_class = [
                                                'pending' => 'warning',
                                                'assigned' => 'info',
                                                'collected' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $class = $badge_class[$request['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $class; ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="text-success mb-3">Contact Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Donor:</th>
                                        <td><?php echo htmlspecialchars($request['donor_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Receiver:</th>
                                        <td><?php echo htmlspecialchars($request['receiver_name']); ?></td>
                                    </tr>
                                    <?php if ($request['volunteer_name']): ?>
                                    <tr>
                                        <th>Volunteer:</th>
                                        <td><?php echo htmlspecialchars($request['volunteer_name']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                                
                                <?php if ($request['notes']): ?>
                                <h6 class="text-muted mb-2 mt-3">Notes</h6>
                                <p><?php echo nl2br(htmlspecialchars($request['notes'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="requests.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            
                            <?php if ($role == 'volunteer' && $request['status'] == 'pending'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="assign">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-user-check"></i> Accept Delivery
                                </button>
                            </form>
                            <?php elseif ($role == 'volunteer' && $request['status'] == 'assigned' && $request['volunteer_id'] == $current_user['user_id']): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="complete">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle"></i> Mark as Complete
                                </button>
                            </form>
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
