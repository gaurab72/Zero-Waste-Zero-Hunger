-- Database: modern_food_waste_system_db

CREATE DATABASE IF NOT EXISTS modern_food_waste_system_db;
USE modern_food_waste_system_db;

-- 1. Users Table (Enhanced)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('donor', 'ngo', 'admin', 'volunteer') DEFAULT 'donor',
    location VARCHAR(100),
    contact_number VARCHAR(20),
    kyc_status ENUM('pending', 'submitted', 'approved', 'rejected') DEFAULT 'pending',
    kyc_file VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Food Listings Table (with Location)
CREATE TABLE IF NOT EXISTS food_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    food_type ENUM('veg', 'non-veg', 'vegan', 'mixed') DEFAULT 'veg',
    quantity VARCHAR(50) NOT NULL,
    expiry_datetime DATETIME NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    pickup_address TEXT,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    status ENUM('available', 'claimed', 'collected', 'expired') DEFAULT 'available',
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Claims / Requests Table
CREATE TABLE IF NOT EXISTS claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    ngo_id INT NOT NULL,
    volunteer_id INT DEFAULT NULL,
    status ENUM('pending', 'assigned', 'picked_up', 'completed', 'approved', 'rejected') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES food_listings(id) ON DELETE CASCADE,
    FOREIGN KEY (ngo_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 4. Chat Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(20) DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Money Donations
CREATE TABLE IF NOT EXISTS money_donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL, -- Donor (if logged in)
    receiver_id INT DEFAULT NULL, -- Verified NGO receiving the donation
    donor_name VARCHAR(100) DEFAULT 'Anonymous',
    amount DECIMAL(10, 2) NOT NULL,
    message TEXT,
    is_anonymous TINYINT(1) DEFAULT 0,
    payment_method VARCHAR(30) DEFAULT 'card',        -- esewa | khalti | paypal | bank | card
    transaction_id VARCHAR(100) DEFAULT NULL UNIQUE,  -- eSewa transaction_code / gateway ref
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Migration: run these if the table already exists (safe to run multiple times)
-- ALTER TABLE money_donations ADD COLUMN IF NOT EXISTS payment_method VARCHAR(30) DEFAULT 'card';
-- ALTER TABLE money_donations ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(100) DEFAULT NULL;
-- ALTER TABLE money_donations ADD UNIQUE IF NOT EXISTS (transaction_id);

-- 7. Admin Logs
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50),
    user_id INT,
    details TEXT,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Feedbacks
CREATE TABLE IF NOT EXISTS feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(100),
    email VARCHAR(100),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    rating INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 9. User Locations (Real-time tracking for Volunteers)
CREATE TABLE IF NOT EXISTS user_locations (
    user_id INT PRIMARY KEY,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

