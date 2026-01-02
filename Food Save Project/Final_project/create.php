<?php
require_once('database.php');
require_once('functions.php');

requireLogin();
requireRole('donor');

$current_user = getCurrentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food_name = sanitize($_POST['food_name'] ?? '');
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unit = sanitize($_POST['unit'] ?? 'kg');
    $expiry_date = sanitize($_POST['expiry_date'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $pickup_location = sanitize($_POST['pickup_location'] ?? '');
    
    // Validation
    if (empty($food_name)) {
        $errors[] = 'Food name is required.';
    }
    if ($quantity <= 0) {
        $errors[] = 'Quantity must be greater than 0.';
    }
    if (empty($pickup_location)) {
        $errors[] = 'Pickup location is required.';
    }
    
    if (empty($errors)) {
        $donation_data = [
            'donor_id' => $current_user['user_id'],
            'food_name' => $food_name,
            'quantity' => $quantity,
            'unit' => $unit,
            'expiry_date' => $expiry_date ?: null,
            'description' => $description,
            'pickup_location' => $pickup_location
        ];
        
        $donation_id = createDonation($donation_data);
        
        if ($donation_id) {
            setFlash('success', 'Donation created successfully!');
            header('Location: donations_index.php');
            exit();
        } else {
            $errors[] = 'Failed to create donation. Please try again.';
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
    <title>Create Donation - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-plus-circle"></i> Create New Donation
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="food_name" class="form-label">Food Name *</label>
                                    <input type="text" class="form-control" id="food_name" name="food_name" required>
                                </div>
                                
                                <div class="col-md-2 mb-3">
                                    <label for="quantity" class="form-label">Quantity *</label>
                                    <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                                </div>
                                
                                <div class="col-md-2 mb-3">
                                    <label for="unit" class="form-label">Unit *</label>
                                    <select class="form-select" id="unit" name="unit" required>
                                        <option value="kg">kg</option>
                                        <option value="liters">liters</option>
                                        <option value="packets">packets</option>
                                        <option value="boxes">boxes</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date (Optional)</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                            </div>
                            
                            <div class="mb-3">
                                <label for="pickup_location" class="form-label">Pickup Location *</label>
                                <input type="text" class="form-control" id="pickup_location" name="pickup_location" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="donations_index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Create Donation
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
