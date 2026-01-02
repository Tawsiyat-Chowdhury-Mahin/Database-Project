<?php
require_once 'database.php';
require_once 'functions.php';
requireLogin();
requireRole('receiver');

$current_user = getCurrentUser();
$donation_id = $_GET['id'] ?? 0;
$donation = getDonationById($donation_id);

if (!$donation || $donation['status'] != 'available') {
    setFlash('danger', 'Donation not available.');
    header('Location: donations_index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($errors)) {
        $request_data = [
            'donation_id' => $donation_id,
            'receiver_id' => $current_user['user_id'],
            'notes' => $notes
        ];
        
        $request_id = createRequest($request_data);
        
        if ($request_id) {
            setFlash('success', 'Request submitted successfully!');
            header('Location: requests.php');
            exit;
        } else {
            $errors[] = 'Failed to create request. Please try again.';
        }
    }
    
    if (!empty($errors)) {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Donation - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-hand-paper"></i> Request Donation
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5>Donation Details:</h5>
                            <p class="mb-1"><strong>Food:</strong> <?php echo htmlspecialchars($donation['food_name']); ?></p>
                            <p class="mb-1"><strong>Quantity:</strong> <?php echo htmlspecialchars($donation['quantity'] . ' ' . $donation['unit']); ?></p>
                            <p class="mb-0"><strong>Location:</strong> <?php echo htmlspecialchars($donation['location']); ?></p>
                        </div>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add any special instructions or requirements..."></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="view_donation.php?id=<?php echo $donation_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Submit Request
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
