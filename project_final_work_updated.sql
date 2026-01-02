-- ====================================================================
-- FOOD WASTE MANAGEMENT SYSTEM - ENHANCED DATABASE
-- ====================================================================
-- Database: food_waste_db
-- Version: 2.0 - Enhanced for complete functionality
-- Updated: January 1, 2026
-- ====================================================================
-- Team Members:
--   PERSON 1 (Priyanti): Authentication & User Management
--   PERSON 2 (Mahin): Donation Management & Request Management 
--   PERSON 3 (Irisha Urbi): Admin Panel & Backend Design
-- ====================================================================
-- Default Test Password: password123 (hashed)
-- Admin Password: admin123 (hashed)
-- ====================================================================

DROP DATABASE IF EXISTS food_waste_db;

CREATE DATABASE food_waste_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE food_waste_db;

-- ====================================================================
-- SECTION 1: CORE TABLES
-- ====================================================================

-- ==================== TABLE 1: USERS (ENHANCED) ====================
-- Added: vehicle_type for volunteers, updated_at timestamp
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'donor', 'receiver', 'volunteer') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    organization VARCHAR(100),
    vehicle_type VARCHAR(50) DEFAULT NULL,  -- NEW: For volunteers
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  -- NEW
    is_active BOOLEAN DEFAULT TRUE,
    
    INDEX idx_role (role),
    INDEX idx_email (email),
    INDEX idx_active (is_active),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores all system users';

-- ==================== TABLE 2: DONATIONS (ENHANCED) ====================
-- Added: food_type, pickup times, photo, portions unit
CREATE TABLE donations (
    donation_id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT NOT NULL,
    food_name VARCHAR(100) NOT NULL,
    food_type ENUM('fresh', 'cooked', 'packaged', 'bakery', 'dairy', 'other') DEFAULT 'other',  -- NEW
    quantity DECIMAL(10,2) NOT NULL,
    unit ENUM('kg', 'portions', 'pieces', 'liters', 'packets', 'boxes') DEFAULT 'kg',  -- UPDATED
    expiry_date DATE,
    status ENUM('available', 'reserved', 'collected', 'expired', 'cancelled') DEFAULT 'available',  -- UPDATED
    description TEXT,
    location VARCHAR(255) NOT NULL,
    pickup_start_time TIME,  -- NEW
    pickup_end_time TIME,  -- NEW
    photo VARCHAR(255) DEFAULT NULL,  -- NEW
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  -- NEW
    
    FOREIGN KEY (donor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    
    INDEX idx_status (status),
    INDEX idx_donor (donor_id),
    INDEX idx_food_type (food_type),  -- NEW
    INDEX idx_expiry (expiry_date),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores all food donations';

-- ==================== TABLE 3: REQUESTS (ENHANCED) ====================
-- Added: assigned_at, completed_at timestamps
CREATE TABLE requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    receiver_id INT NOT NULL,
    volunteer_id INT DEFAULT NULL,
    status ENUM('pending', 'assigned', 'collected', 'cancelled') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_at TIMESTAMP NULL,  -- NEW
    completed_at TIMESTAMP NULL,  -- NEW
    notes TEXT,
    
    FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(user_id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_receiver (receiver_id),
    INDEX idx_volunteer (volunteer_id),
    INDEX idx_donation (donation_id),
    INDEX idx_request_date (request_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores donation requests';

-- ==================== TABLE 4: ACTIVITY LOGS ====================
-- Unchanged - for tracking system activities
CREATE TABLE activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    
    INDEX idx_user_action (user_id, action),
    INDEX idx_created (created_at),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='System activity logging';

-- ====================================================================
-- SECTION 2: SAMPLE DATA (UPDATED WITH MORE REALISTIC DATA)
-- ====================================================================

-- ==================== INSERT USERS (ENHANCED) ====================
-- Password for all users: password123
-- Admin password: admin123
-- Actual bcrypt hash for "password123": $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- Actual bcrypt hash for "admin123": $2y$10$8LpVsb32VNP1kZwmpG5XF.tFYJ6MB1lbLFy3DKHNB2q8TFb00OcI2

INSERT INTO users (username, email, password_hash, role, full_name, phone, address, organization, vehicle_type, is_active) VALUES
-- Admins (3 users) - All use same password: admin123
('admin04', 'admin04@example.com', '$2y$10$8LpVsb32VNP1kZwmpG5XF.tFYJ6MB1lbLFy3DKHNB2q8TFb00OcI2', 'admin', 'Irisha Urbi', '01710000001', 'Dhaka, Bangladesh', 'FoodCare Foundation', NULL, TRUE),
('admin05', 'admin05@example.com', '$2y$10$8LpVsb32VNP1kZwmpG5XF.tFYJ6MB1lbLFy3DKHNB2q8TFb00OcI2', 'admin', 'Mahin', '01710000002', 'Chattogram, Bangladesh', 'Care Network', NULL, TRUE),
('admin06', 'admin06@example.com', '$2y$10$8LpVsb32VNP1kZwmpG5XF.tFYJ6MB1lbLFy3DKHNB2q8TFb00OcI2', 'admin', 'Priyanti', '01710000003', 'Chattogram, Bangladesh', 'Care Network', NULL, TRUE),

-- Donors (5 users)
('greenrest', 'green@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Rahim Uddin', '01755555555', 'Dhanmondi, Dhaka', 'Green Restaurant', NULL, TRUE),
('freshbakery', 'fresh@bakery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Karim Ahmed', '01766666666', 'Uttara, Dhaka', 'Fresh Bakery', NULL, TRUE),
('carehotel', 'care@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Nabila Khan', '01777777777', 'Mirpur, Dhaka', 'Care Hotel', NULL, TRUE),
('foodcourt', 'info@foodcourt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Fahim Hossain', '01788888888', 'Banani, Dhaka', 'Food Court Plaza', NULL, TRUE),
('megamart', 'mega@mart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Sara Rahman', '01799999999', 'Gulshan, Dhaka', 'Mega Mart Supermarket', NULL, TRUE),

-- Receivers (4 users)
('orphanage', 'hope@orphanage.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receiver', 'Salma Begum', '01700000001', 'Keraniganj, Dhaka', 'Hope Orphanage', NULL, TRUE),
('shelter', 'contact@shelter.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receiver', 'Rashed Mia', '01700000002', 'Gazipur', 'Community Shelter', NULL, TRUE),
('carehome', 'info@carehome.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receiver', 'Hasina Akter', '01700000003', 'Narayanganj', 'Care Home Center', NULL, TRUE),
('relief', 'relief@org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receiver', 'Imran Hossain', '01700000004', 'Savar', 'Relief Organization', NULL, TRUE),

-- Volunteers (4 users with vehicle types)
('volunteer01', 'tanvir@volunteer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'volunteer', 'Tanvir Ahmed', '01700000005', 'Mohakhali, Dhaka', 'Helping Hands', 'Motorcycle', TRUE),
('volunteer02', 'nusrat@volunteer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'volunteer', 'Nusrat Jahan', '01700000006', 'Rampura, Dhaka', 'Food Rescue', 'Car', TRUE),
('volunteer03', 'sabbir@volunteer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'volunteer', 'Sabbir Hossain', '01700000007', 'Khilgaon, Dhaka', 'Care Group', 'Van', TRUE),
('volunteer04', 'mehedi@volunteer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'volunteer', 'Mehedi Hasan', '01700000008', 'Bashundhara, Dhaka', 'Serve Together', 'Truck', TRUE);

-- ==================== INSERT DONATIONS (ENHANCED WITH MORE VARIETY) ====================
INSERT INTO donations (donor_id, food_name, food_type, quantity, unit, expiry_date, status, description, location, pickup_start_time, pickup_end_time) VALUES
-- Available donations
(5, 'Cooked Rice', 'cooked', 25.00, 'kg', '2026-01-05', 'available', 'Freshly cooked rice from lunch service', 'Dhanmondi, Dhaka', '10:00:00', '18:00:00'),
(6, 'Bread Loaves', 'bakery', 40.00, 'pieces', '2026-01-06', 'available', 'Day-old bread in good condition', 'Uttara, Dhaka', '08:00:00', '20:00:00'),
(7, 'Vegetable Curry', 'cooked', 15.50, 'kg', '2026-01-05', 'available', 'Mixed vegetable curry, packed hygienically', 'Mirpur, Dhaka', '12:00:00', '19:00:00'),
(8, 'Fruit Salad', 'fresh', 10.00, 'kg', '2026-01-05', 'available', 'Fresh fruit salad portions', 'Banani, Dhaka', '11:00:00', '17:00:00'),
(9, 'Packaged Snacks', 'packaged', 100.00, 'packets', '2026-02-01', 'available', 'Sealed snack packets near expiry', 'Gulshan, Dhaka', '09:00:00', '21:00:00'),
(6, 'Fresh Milk', 'dairy', 50.00, 'liters', '2026-01-03', 'available', 'Fresh milk from dairy farm', 'Uttara, Dhaka', '06:00:00', '10:00:00'),
(9, 'Mixed Vegetables', 'fresh', 30.00, 'kg', '2026-01-07', 'available', 'Surplus vegetables from market', 'Gulshan, Dhaka', '08:00:00', '16:00:00'),

-- Reserved donation
(5, 'Chicken Biryani', 'cooked', 20.00, 'portions', '2026-01-05', 'reserved', 'Leftover from wedding event', 'Dhanmondi, Dhaka', '14:00:00', '20:00:00'),

-- Collected donation (past)
(7, 'Sandwich Packs', 'packaged', 50.00, 'pieces', '2026-01-02', 'collected', 'Pre-packed sandwiches', 'Mirpur, Dhaka', '07:00:00', '12:00:00');

-- ==================== INSERT REQUESTS (WITH REALISTIC STATUSES) ====================
INSERT INTO requests (donation_id, receiver_id, volunteer_id, status, assigned_at, completed_at, notes) VALUES
-- Assigned request
(1, 10, 14, 'assigned', NOW(), NULL, 'Need for 50 children at orphanage'),

-- Collected request (completed)
(9, 11, 15, 'collected', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 'Successfully delivered to care home'),

-- Pending request (waiting for volunteer)
(3, 12, NULL, 'pending', NULL, NULL, 'Urgent request for evening meal'),

-- Assigned request
(8, 10, 16, 'assigned', DATE_SUB(NOW(), INTERVAL 1 HOUR), NULL, 'Event food for community distribution');

-- ==================== INSERT ACTIVITY LOGS ====================
INSERT INTO activity_logs (user_id, action, table_name, record_id, details) VALUES
(1, 'LOGIN', 'users', 1, 'Admin logged into the system'),
(5, 'CREATE', 'donations', 1, 'Created new food donation entry'),
(10, 'REQUEST', 'requests', 1, 'Requested donation for orphanage'),
(14, 'ASSIGN', 'requests', 1, 'Volunteer assigned to delivery'),
(15, 'COMPLETE', 'requests', 2, 'Delivery completed successfully'),
(1, 'VIEW', 'admin', NULL, 'Accessed admin panel'),
(6, 'CREATE', 'donations', 2, 'Posted fresh bread donation'),
(12, 'REQUEST', 'requests', 3, 'Created urgent food request');

-- ====================================================================
-- SECTION 3: VIEWS FOR EASIER QUERIES
-- ====================================================================

-- View 1: Complete Donation Details with expiry calculation
CREATE VIEW donation_details AS
SELECT 
    d.*,
    u.username AS donor_username,
    u.full_name AS donor_name,
    u.organization AS donor_organization,
    u.phone AS donor_phone,
    u.email AS donor_email,
    DATEDIFF(d.expiry_date, CURDATE()) AS days_until_expiry,
    CASE 
        WHEN d.expiry_date IS NULL THEN 'No expiry'
        WHEN d.expiry_date < CURDATE() THEN 'Expired'
        WHEN DATEDIFF(d.expiry_date, CURDATE()) <= 1 THEN 'Urgent'
        WHEN DATEDIFF(d.expiry_date, CURDATE()) <= 3 THEN 'Soon'
        ELSE 'Good'
    END AS expiry_status
FROM donations d
JOIN users u ON d.donor_id = u.user_id;

-- View 2: Complete Request Details with all contact info
CREATE VIEW request_details AS
SELECT 
    r.*, 
    d.food_name, 
    d.food_type,
    d.quantity, 
    d.unit, 
    d.location AS donation_location,
    donor.full_name AS donor_name,
    donor.phone AS donor_phone,
    donor.organization AS donor_organization,
    receiver.full_name AS receiver_name,
    receiver.phone AS receiver_phone,
    receiver.organization AS receiver_organization,
    volunteer.full_name AS volunteer_name,
    volunteer.phone AS volunteer_phone,
    volunteer.vehicle_type AS volunteer_vehicle
FROM requests r
JOIN donations d ON r.donation_id = d.donation_id
JOIN users donor ON d.donor_id = donor.user_id
JOIN users receiver ON r.receiver_id = receiver.user_id
LEFT JOIN users volunteer ON r.volunteer_id = volunteer.user_id;

-- View 3: User Statistics
CREATE VIEW user_stats AS
SELECT 
    u.user_id, 
    u.username, 
    u.role, 
    u.full_name, 
    u.organization,
    u.is_active,
    COUNT(DISTINCT d.donation_id) AS total_donations,
    COUNT(DISTINCT CASE WHEN d.status = 'collected' THEN d.donation_id END) AS successful_donations,
    COUNT(DISTINCT r_receiver.request_id) AS total_requests,
    COUNT(DISTINCT CASE WHEN r_receiver.status = 'collected' THEN r_receiver.request_id END) AS collected_requests,
    COUNT(DISTINCT r_volunteer.request_id) AS volunteer_assignments,
    COUNT(DISTINCT CASE WHEN r_volunteer.status = 'collected' THEN r_volunteer.request_id END) AS completed_deliveries
FROM users u
LEFT JOIN donations d ON u.user_id = d.donor_id
LEFT JOIN requests r_receiver ON u.user_id = r_receiver.receiver_id
LEFT JOIN requests r_volunteer ON u.user_id = r_volunteer.volunteer_id
GROUP BY u.user_id, u.username, u.role, u.full_name, u.organization, u.is_active;

-- View 4: Available Donations (Quick access)
CREATE VIEW available_donations AS
SELECT 
    d.*,
    u.full_name AS donor_name,
    u.organization,
    u.phone AS donor_phone,
    DATEDIFF(d.expiry_date, CURDATE()) AS days_until_expiry
FROM donations d
JOIN users u ON d.donor_id = u.user_id
WHERE d.status = 'available'
AND (d.expiry_date IS NULL OR d.expiry_date >= CURDATE())
ORDER BY d.expiry_date ASC, d.created_at DESC;

-- View 5: Pending Requests (Quick access for admin/volunteers)
CREATE VIEW pending_requests AS
SELECT 
    r.*,
    d.food_name,
    d.food_type,
    d.quantity,
    d.unit,
    d.location,
    donor.full_name AS donor_name,
    donor.phone AS donor_phone,
    receiver.full_name AS receiver_name,
    receiver.organization AS receiver_organization,
    receiver.phone AS receiver_phone
FROM requests r
JOIN donations d ON r.donation_id = d.donation_id
JOIN users donor ON d.donor_id = donor.user_id
JOIN users receiver ON r.receiver_id = receiver.user_id
WHERE r.status = 'pending'
ORDER BY r.request_date ASC;

-- ====================================================================
-- SECTION 4: STORED PROCEDURES
-- ====================================================================

-- Procedure 1: Log Activities
DELIMITER //
CREATE PROCEDURE LogActivity(
    IN p_user_id INT,
    IN p_action VARCHAR(100),
    IN p_table_name VARCHAR(50),
    IN p_record_id INT,
    IN p_details TEXT
)
BEGIN
    INSERT INTO activity_logs (user_id, action, table_name, record_id, details)
    VALUES (p_user_id, p_action, p_table_name, p_record_id, p_details);
END //
DELIMITER ;

-- ====================================================================
-- SECTION 5: TRIGGERS
-- ====================================================================

-- Trigger 1: Auto-update donation status when request assigned
DELIMITER //
CREATE TRIGGER after_request_assigned
AFTER UPDATE ON requests
FOR EACH ROW
BEGIN
    -- When request status changes to assigned
    IF OLD.status = 'pending' AND NEW.status = 'assigned' THEN
        UPDATE donations SET status = 'reserved' WHERE donation_id = NEW.donation_id;
        CALL LogActivity(NEW.volunteer_id, 'ASSIGN_REQUEST', 'requests', NEW.request_id,
            CONCAT('Assigned to request ID ', NEW.request_id));
    END IF;
    
    -- When request status changes to collected
    IF NEW.status = 'collected' AND OLD.status != 'collected' THEN
        UPDATE donations SET status = 'collected' WHERE donation_id = NEW.donation_id;
        CALL LogActivity(NEW.volunteer_id, 'COMPLETE_REQUEST', 'requests', NEW.request_id,
            CONCAT('Completed request ID ', NEW.request_id));
    END IF;
END //
DELIMITER ;

-- Trigger 2: Auto-reserve donation when request created
DELIMITER //
CREATE TRIGGER after_request_created
AFTER INSERT ON requests
FOR EACH ROW
BEGIN
    UPDATE donations SET status = 'reserved' 
    WHERE donation_id = NEW.donation_id AND status = 'available';
    
    CALL LogActivity(NEW.receiver_id, 'CREATE_REQUEST', 'requests', NEW.request_id,
        CONCAT('Created request for donation ID ', NEW.donation_id));
END //
DELIMITER ;

-- ====================================================================
-- SECTION 6: EVENTS
-- ====================================================================

-- Event: Auto-expire old donations (runs daily at midnight)
DELIMITER //
CREATE EVENT IF NOT EXISTS expire_donations
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Mark expired donations
    UPDATE donations 
    SET status = 'expired' 
    WHERE status IN ('available', 'reserved')
    AND expiry_date < CURDATE();
    
    -- Log the expiration action
    INSERT INTO activity_logs (user_id, action, table_name, record_id, details)
    SELECT NULL, 'AUTO_EXPIRE', 'donations', donation_id, 
           CONCAT('Auto-expired donation: ', food_name)
    FROM donations 
    WHERE status = 'expired' 
    AND DATE(updated_at) = CURDATE();
END //
DELIMITER ;

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- ====================================================================
-- SECTION 7: VERIFICATION QUERIES
-- ====================================================================

-- Check all tables created
SELECT 'Database Created Successfully!' AS Status;
SHOW TABLES;

-- Check user counts by role
SELECT 
    role, 
    COUNT(*) as user_count,
    SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END) as active_count
FROM users 
GROUP BY role
ORDER BY role;

-- Check donation counts by status
SELECT 
    status, 
    COUNT(*) as count,
    SUM(quantity) as total_quantity
FROM donations 
GROUP BY status
ORDER BY 
    FIELD(status, 'available', 'reserved', 'collected', 'expired', 'cancelled');

-- Check request counts by status
SELECT 
    status, 
    COUNT(*) as count 
FROM requests 
GROUP BY status
ORDER BY 
    FIELD(status, 'pending', 'assigned', 'collected', 'cancelled');

-- Final system summary
SELECT 
    'System Ready!' as message,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE is_active = TRUE) as active_users,
    (SELECT COUNT(*) FROM donations) as total_donations,
    (SELECT COUNT(*) FROM donations WHERE status = 'available') as available_donations,
    (SELECT COUNT(*) FROM requests) as total_requests,
    (SELECT COUNT(*) FROM requests WHERE status = 'pending') as pending_requests,
    (SELECT COUNT(*) FROM requests WHERE status = 'collected') as completed_deliveries,
    (SELECT COUNT(*) FROM activity_logs) as total_logs;

-- Show available donations
SELECT 'Available Donations:' as Info;
SELECT * FROM available_donations;

-- Show pending requests
SELECT 'Pending Requests:' as Info;
SELECT * FROM pending_requests;

-- ====================================================================
-- SECTION 8: LOGIN CREDENTIALS REFERENCE
-- ====================================================================

SELECT '=== LOGIN CREDENTIALS ===' AS '*** TEST ACCOUNTS ***';

SELECT 
    role,
    email,
    CASE 
        WHEN role = 'admin' THEN 'admin123'
        ELSE 'password123'
    END as password,
    full_name
FROM users
WHERE email IN (
    'admin04@example.com',
    'green@restaurant.com', 
    'hope@orphanage.org',
    'tanvir@volunteer.com'
)
ORDER BY 
    FIELD(role, 'admin', 'donor', 'receiver', 'volunteer');

-- ====================================================================
-- END OF SQL FILE
-- ====================================================================
