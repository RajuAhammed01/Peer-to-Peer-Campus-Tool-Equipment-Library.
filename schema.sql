-- Campus Tool & Equipment Library (CSE 3120 Open-Ended Experiment)
-- Database Schema for MySQL

CREATE DATABASE IF NOT EXISTS `peerShare` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `peerShare`;

-- Drop existing tables if re-initializing
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `borrow_requests`;
DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `student_id` VARCHAR(30) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('Student', 'Faculty', 'Lab Admin') DEFAULT 'Student',
  `department` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `reputation_score` DECIMAL(3,2) DEFAULT 5.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories Table
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL UNIQUE,
  `icon` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL
) ENGINE=InnoDB;

-- Equipment / Items Table
CREATE TABLE `items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `item_condition` ENUM('Brand New', 'Like New', 'Good', 'Fair') DEFAULT 'Good',
  `daily_fee` DECIMAL(8,2) DEFAULT 0.00,
  `security_deposit` DECIMAL(8,2) DEFAULT 0.00,
  `status` ENUM('available', 'borrowed', 'maintenance') DEFAULT 'available',
  `e_waste_kg` DECIMAL(6,2) DEFAULT 1.50,
  `location` VARCHAR(100) NOT NULL,
  `image_icon` VARCHAR(100) DEFAULT 'fa-microchip',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Borrow Requests & Transactions Table
CREATE TABLE `borrow_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT NOT NULL,
  `borrower_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `purpose` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'active', 'returned', 'cancelled') DEFAULT 'pending',
  `total_cost` DECIMAL(8,2) DEFAULT 0.00,
  `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`borrower_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- User Reviews & Ratings Table
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `borrow_request_id` INT NOT NULL,
  `reviewer_id` INT NOT NULL,
  `rating` INT CHECK (`rating` BETWEEN 1 AND 5),
  `comment` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed Initial Categories
INSERT INTO `categories` (`id`, `name`, `icon`, `description`) VALUES
(1, 'Electronics & Microcontrollers', 'fa-microchip', 'Arduino, Raspberry Pi, ESP32, sensors, development boards'),
(2, 'Test & Measurement', 'fa-wave-square', 'Oscilloscopes, digital multimeters, signal generators, power supplies'),
(3, 'Robotics & Mechatronics', 'fa-robot', 'Servo motors, motor drivers, chassis, robotic arms, IMUs'),
(4, 'Photography & Media', 'fa-camera', 'DSLR cameras, lenses, tripods, lighting, audio mics'),
(5, 'Lab Equipment & Tools', 'fa-tools', 'Soldering stations, 3D pen, wire strippers, heat guns, toolkits'),
(6, 'Academic Textbooks & Notes', 'fa-book', 'Engineering reference books, lab manuals, course textbooks');

-- Seed Sample Users (Password is 'password123')
INSERT INTO `users` (`id`, `full_name`, `email`, `student_id`, `password_hash`, `role`, `department`, `reputation_score`) VALUES
(1, 'Raju Ahmed', 'raju@ulab.edu.bd', '213014001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWef5eUO', 'Student', 'Computer Science & Engineering', 4.90),
(2, 'Sadia Rahman', 'sadia.cse@ulab.edu.bd', '213014050', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWef5eUO', 'Student', 'Computer Science & Engineering', 4.95),
(3, 'Dr. Tanvir Hossain', 'tanvir.hossain@ulab.edu.bd', 'FAC-5092', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWef5eUO', 'Faculty', 'Computer Science & Engineering', 5.00),
(4, 'Nabila Islam', 'nabila.eee@ulab.edu.bd', '221015012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWef5eUO', 'Student', 'Electrical & Electronic Engineering', 4.85);

-- Seed Sample Equipment Items
INSERT INTO `items` (`id`, `owner_id`, `category_id`, `title`, `description`, `item_condition`, `daily_fee`, `security_deposit`, `status`, `e_waste_kg`, `location`, `image_icon`) VALUES
(1, 1, 1, 'Arduino Mega 2560 Sensor Kit', 'Complete sensor kit with 37 sensors, jumper wires, OLED display, and breadboards. Perfect for CSE3120 IoT projects.', 'Like New', 0.00, 500.00, 'available', 1.20, 'Campus Building A, Lab 402', 'fa-microchip'),
(2, 2, 2, 'Rigol DS1054Z 50MHz Digital Oscilloscope', '4-channel digital storage oscilloscope with probes. Ideal for signal analysis in EEE / embedded labs.', 'Good', 50.00, 2000.00, 'available', 3.50, 'Library Quiet Zone / Study Room B', 'fa-wave-square'),
(3, 3, 4, 'Canon EOS 80D DSLR with 18-135mm Lens', 'Great for documentary projects, campus events, and computer vision lab datasets.', 'Like New', 100.00, 3000.00, 'available', 1.80, 'CSE Department Faculty Office', 'fa-camera'),
(4, 1, 5, 'Hakko Digital Soldering Station & Heat Gun', 'Temperature-controlled soldering iron station with SMD rework heat gun and solder wire set.', 'Good', 0.00, 800.00, 'available', 2.10, 'MakerSpace Workshop 101', 'fa-fire'),
(5, 4, 3, '4WD Robotic Chassis + L298N Driver + Servo Set', 'Pre-assembled 4-wheel drive robot chassis with motor driver module and HC-SR04 ultrasonic sensors.', 'Good', 0.00, 400.00, 'borrowed', 0.90, 'EEE Lab 3', 'fa-robot'),
(6, 2, 6, 'Operating System Concepts (Silberschatz 10th Ed)', 'Hardcover textbook for Operating Systems course in pristine condition with quick reference bookmarks.', 'Brand New', 0.00, 200.00, 'available', 1.40, 'Student Lounge Building B', 'fa-book');

-- Seed Sample Borrow Requests
INSERT INTO `borrow_requests` (`id`, `item_id`, `borrower_id`, `start_date`, `end_date`, `purpose`, `status`, `total_cost`) VALUES
(1, 5, 1, '2026-08-01', '2026-08-05', 'Testing autonomous obstacle avoidance robot for embedded systems final lab demo.', 'approved', 0.00),
(2, 2, 4, '2026-08-02', '2026-08-04', 'Measuring PWM pulse signal waveforms for Power Electronics assignment.', 'pending', 100.00);
