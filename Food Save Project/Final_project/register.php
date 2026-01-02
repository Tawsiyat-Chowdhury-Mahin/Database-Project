<?php
require_once 'database.php';
require_once 'functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $organization = sanitize($_POST['organization'] ?? '');
    
    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    } else {
        // ✅ FIX: Check if username already exists
        $existing_username = getUserByUsername($username);
        if ($existing_username) {
            $errors[] = 'Username already taken. Please choose another.';
        }
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        // Check if email already exists
        $existing_user = getUserByEmail($email);
        if ($existing_user) {
            $errors[] = 'Email already registered. Please login.';
        }
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    
    if ($password != $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($role) || !in_array($role, ['donor', 'receiver', 'volunteer', 'admin'])) {
        $errors[] = 'Please select a valid role.';
    }
    
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    }
    
    // If no errors, create user
    if (empty($errors)) {
        $user_data = [
            'username' => $username,
            'email' => $email,
            'password_hash' => hashPassword($password),
            'role' => $role,
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address,
            'organization' => $organization
        ];
        
        $user_id = createUser($user_data);
        
        if ($user_id) {
            setFlash('success', 'Registration successful! Please login with your credentials.');
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Failed to create account. Please try again.';
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
    <title>Register - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">
                            <i class="fas fa-user-plus text-success"></i> Create Account
                        </h2>
                        <p class="text-center text-muted mb-4">
                            Join FoodSave to help reduce food waste and feed those in need
                        </p>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                    <small class="text-muted">At least 3 characters</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="text-muted">At least 6 characters</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">I want to register as</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">-- Select Role --</option>
                                    <option value="donor" <?php echo (isset($_POST['role']) && $_POST['role'] == 'donor') ? 'selected' : ''; ?>>
                                        Donor (Restaurant, Supermarket, Individual)
                                    </option>
                                    <option value="receiver" <?php echo (isset($_POST['role']) && $_POST['role'] == 'receiver') ? 'selected' : ''; ?>>
                                        Receiver (Charity, Shelter, NGO)
                                    </option>
                                    <option value="volunteer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'volunteer') ? 'selected' : ''; ?>>
                                        Volunteer (Help with delivery)
                                    </option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                           placeholder="01XXXXXXXXX" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="organization" class="form-label">Organization (Optional)</label>
                                    <input type="text" class="form-control" id="organization" name="organization" 
                                           value="<?php echo htmlspecialchars($_POST['organization'] ?? ''); ?>" 
                                           placeholder="Company/NGO name">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-success">Terms and Conditions</a>
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-user-plus"></i> Register
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <p class="text-center mb-0">
                            Already have an account? 
                            <a href="login.php" class="text-success fw-bold">Login here</a>
                        </p>
                    </div>
                </div>
                
                <!-- Role Information -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Role Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <h6 class="text-primary"><i class="fas fa-store"></i> Donor</h6>
                                <small>For restaurants, supermarkets, and individuals who want to donate surplus food.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <h6 class="text-info"><i class="fas fa-home"></i> Receiver</h6>
                                <small>For charities, shelters, and NGOs who need food donations for those in need.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <h6 class="text-warning"><i class="fas fa-hands-helping"></i> Volunteer</h6>
                                <small>For individuals who want to help transport food from donors to receivers.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 FoodSave - Food Waste Reduction System. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            // You can add visual feedback here
        });
        
        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password != confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
