<?php
require_once 'database.php';

// ====================================================================
// USER FUNCTIONS
// ====================================================================

// Get user by ID
function getUserById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get user by email
function getUserByEmail($email) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// ✅ Get user by username (FIX FOR REGISTRATION)
function getUserByUsername($username) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Create new user
function createUser($data) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone, address, organization) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss",
        $data['username'],
        $data['email'],
        $data['password_hash'],
        $data['role'],
        $data['full_name'],
        $data['phone'],
        $data['address'],
        $data['organization']
    );
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    
    return false;
}

// ====================================================================
// DONATION FUNCTIONS
// ====================================================================

// Get all donations with optional filters
function getAllDonations($filters = []) {
    $conn = getDBConnection();
    $query = "SELECT d.*, u.full_name as donor_name, u.organization
              FROM donations d
              JOIN users u ON d.donor_id = u.user_id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Apply filters
    if (isset($filters['donor_id'])) {
        $query .= " AND d.donor_id = ?";
        $params[] = $filters['donor_id'];
        $types .= "i";
    }
    
    if (isset($filters['status'])) {
        $query .= " AND d.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    
    // ORDER BY must come before LIMIT
    $query .= " ORDER BY d.created_at DESC";
    
    // Add LIMIT if specified
    if (isset($filters['limit'])) {
        $query .= " LIMIT ?";
        $params[] = $filters['limit'];
        $types .= "i";
    }
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $donations = [];
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
    }
    
    return $donations;
}

// Get donation by ID
function getDonationById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT d.*, u.full_name as donor_name, u.organization
                            FROM donations d
                            JOIN users u ON d.donor_id = u.user_id
                            WHERE d.donation_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Create new donation
function createDonation($data) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO donations (donor_id, food_name, quantity, unit, expiry_date, description, location)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss",
        $data['donor_id'],
        $data['food_name'],
        $data['quantity'],
        $data['unit'],
        $data['expiry_date'],
        $data['description'],
        $data['pickup_location']
    );
    
    if ($stmt->execute()) {
        $donation_id = $conn->insert_id;
        return $donation_id;
    }
    
    return false;
}

// Update donation status
function updateDonationStatus($id, $status) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE donations SET status = ? WHERE donation_id = ?");
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
}

// Get all available donations
function getAvailableDonations() {
    $conn = getDBConnection();
    $query = "SELECT d.*, u.full_name as donor_name, u.organization
              FROM donations d
              JOIN users u ON d.donor_id = u.user_id
              WHERE d.status = 'available'
              AND (d.expiry_date IS NULL OR d.expiry_date > CURDATE())
              ORDER BY d.expiry_date ASC";
    
    $result = $conn->query($query);
    $donations = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $donations[] = $row;
        }
    }
    
    return $donations;
}

// ====================================================================
// REQUEST FUNCTIONS
// ====================================================================

// Get all requests with optional filters
function getAllRequests($filters = []) {
    $conn = getDBConnection();
    $query = "SELECT r.*, d.food_name, d.quantity, d.unit,
              donor.full_name as donor_name,
              receiver.full_name as receiver_name,
              volunteer.full_name as volunteer_name
              FROM requests r
              JOIN donations d ON r.donation_id = d.donation_id
              JOIN users donor ON d.donor_id = donor.user_id
              JOIN users receiver ON r.receiver_id = receiver.user_id
              LEFT JOIN users volunteer ON r.volunteer_id = volunteer.user_id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Apply filters
    if (isset($filters['receiver_id'])) {
        $query .= " AND r.receiver_id = ?";
        $params[] = $filters['receiver_id'];
        $types .= "i";
    }
    
    if (isset($filters['volunteer_id'])) {
        $query .= " AND r.volunteer_id = ?";
        $params[] = $filters['volunteer_id'];
        $types .= "i";
    }
    
    if (isset($filters['donor_id'])) {
        $query .= " AND d.donor_id = ?";
        $params[] = $filters['donor_id'];
        $types .= "i";
    }
    
    if (isset($filters['status'])) {
        $query .= " AND r.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    
    // ORDER BY must come before LIMIT
    $query .= " ORDER BY r.request_date DESC";
    
    // Add LIMIT if specified
    if (isset($filters['limit'])) {
        $query .= " LIMIT ?";
        $params[] = $filters['limit'];
        $types .= "i";
    }
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    return $requests;
}

// ✅ Get pending requests (not assigned to any volunteer) - FIX FOR VOLUNTEERS
function getPendingRequests($limit = 10) {
    $conn = getDBConnection();
    $query = "SELECT r.*, d.food_name, d.quantity, d.unit, d.location,
              donor.full_name as donor_name,
              receiver.full_name as receiver_name
              FROM requests r
              JOIN donations d ON r.donation_id = d.donation_id
              JOIN users donor ON d.donor_id = donor.user_id
              JOIN users receiver ON r.receiver_id = receiver.user_id
              WHERE r.status = 'pending' AND r.volunteer_id IS NULL
              ORDER BY r.request_date DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    return $requests;
}

// Get request by ID
function getRequestById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT r.*, d.food_name, d.quantity, d.unit, d.location as pickup_location,
                            donor.full_name as donor_name,
                            receiver.full_name as receiver_name,
                            volunteer.full_name as volunteer_name
                            FROM requests r
                            JOIN donations d ON r.donation_id = d.donation_id
                            JOIN users donor ON d.donor_id = donor.user_id
                            JOIN users receiver ON r.receiver_id = receiver.user_id
                            LEFT JOIN users volunteer ON r.volunteer_id = volunteer.user_id
                            WHERE r.request_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Create new request
function createRequest($data) {
    $conn = getDBConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create request
        $stmt = $conn->prepare("INSERT INTO requests (donation_id, receiver_id, notes)
                                VALUES (?, ?, ?)");
        $stmt->bind_param("iis",
            $data['donation_id'],
            $data['receiver_id'],
            $data['notes']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create request");
        }
        
        $request_id = $conn->insert_id;
        
        // Update donation status
        $updateStmt = $conn->prepare("UPDATE donations SET status = 'reserved' WHERE donation_id = ?");
        $updateStmt->bind_param("i", $data['donation_id']);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update donation status");
        }
        
        // Commit transaction
        $conn->commit();
        return $request_id;
        
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// ✅ UPDATED: Assign volunteer to request and mark as collected (DONOR SEES SUCCESSFUL)
function assignRequest($request_id, $volunteer_id) {
    $conn = getDBConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get the request first to get donation_id
        $checkStmt = $conn->prepare("SELECT donation_id, status FROM requests WHERE request_id = ?");
        $checkStmt->bind_param("i", $request_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $request = $result->fetch_assoc();
        
        if (!$request || $request['status'] != 'pending') {
            throw new Exception("Request not available");
        }
        
        // Update request with volunteer and status to 'collected'
        $stmt = $conn->prepare("UPDATE requests SET volunteer_id = ?, status = 'collected' 
                                WHERE request_id = ?");
        $stmt->bind_param("ii", $volunteer_id, $request_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to assign request");
        }
        
        // Update donation status to 'collected' (DONOR SEES THIS AS SUCCESSFUL!)
        $updateStmt = $conn->prepare("UPDATE donations SET status = 'collected' WHERE donation_id = ?");
        $updateStmt->bind_param("i", $request['donation_id']);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update donation status");
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Complete request (mark as collected) - Now redundant but kept for compatibility
function completeRequest($request_id) {
    $conn = getDBConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update request status
        $stmt = $conn->prepare("UPDATE requests SET status = 'collected' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update request");
        }
        
        // Update donation status
        $request = getRequestById($request_id);
        $updateStmt = $conn->prepare("UPDATE donations SET status = 'collected' WHERE donation_id = ?");
        $updateStmt->bind_param("i", $request['donation_id']);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update donation");
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// ====================================================================
// NOTIFICATION FUNCTIONS
// ====================================================================

// Create notification
function createNotification($user_id, $title, $message) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $message);
    return $stmt->execute();
}

// Get unread notification count
function getUnreadNotificationCount($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM activity_logs 
                            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    return 0;
}

// Get user notifications
function getNotifications($user_id, $limit = 10) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

// ====================================================================
// STATISTICS FUNCTIONS
// ====================================================================

// Get donor statistics
function getDonorStats($donor_id) {
    $conn = getDBConnection();
    $stats = [];
    
    // Total donations
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM donations WHERE donor_id = ?");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_donations'] = $result->fetch_assoc()['count'];
    
    // Total kg donated
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM donations WHERE donor_id = ? AND unit = 'kg'");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_kg'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Successful donations (status = 'collected')
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM donations WHERE donor_id = ? AND status = 'collected'");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['successful_donations'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// Get receiver statistics
function getReceiverStats($receiver_id) {
    $conn = getDBConnection();
    $stats = [];
    
    // Total requests
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM requests WHERE receiver_id = ?");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_requests'] = $result->fetch_assoc()['count'];
    
    // Collected requests
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM requests WHERE receiver_id = ? AND status = 'collected'");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['collected_requests'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// Get volunteer statistics
function getVolunteerStats($volunteer_id) {
    $conn = getDBConnection();
    $stats = [];
    
    // Total deliveries
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM requests WHERE volunteer_id = ?");
    $stmt->bind_param("i", $volunteer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_deliveries'] = $result->fetch_assoc()['count'];
    
    // Completed deliveries
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM requests WHERE volunteer_id = ? AND status = 'collected'");
    $stmt->bind_param("i", $volunteer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['completed_deliveries'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// Get admin statistics
function getAdminStats() {
    $conn = getDBConnection();
    $stats = [];
    
    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $stats['total_users'] = $result->fetch_assoc()['count'];
    }
    
    // Available donations
    $result = $conn->query("SELECT COUNT(*) as count FROM donations WHERE status = 'available'");
    if ($result) {
        $stats['available_donations'] = $result->fetch_assoc()['count'];
    }
    
    // Pending requests
    $result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending'");
    if ($result) {
        $stats['pending_requests'] = $result->fetch_assoc()['count'];
    }
    
    // Completed deliveries
    $result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'collected'");
    if ($result) {
        $stats['completed_deliveries'] = $result->fetch_assoc()['count'];
    }
    
    return $stats;
}

// Get all users (for admin)
function getAllUsers() {
    $conn = getDBConnection();
    $query = "SELECT * FROM users ORDER BY created_at DESC";
    $result = $conn->query($query);
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}
?>
