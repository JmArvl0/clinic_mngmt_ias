-- ============================================================
-- CLINIC & MEDICAL SERVICES SUB-SYSTEM
-- College/University - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS clinic_system;
USE clinic_system;

-- ============================================================
-- USERS & ROLES (Authentication)
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'nurse', 'doctor') NOT NULL DEFAULT 'nurse',
    status ENUM('active', 'inactive') DEFAULT 'active',
    profile_photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- STUDENTS
-- ============================================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    course VARCHAR(100),
    year_level VARCHAR(20),
    section VARCHAR(50),
    contact_number VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    guardian_name VARCHAR(100),
    guardian_contact VARCHAR(20),
    blood_type ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown') DEFAULT 'Unknown',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- MODULE 1: STUDENT MEDICAL RECORDS
-- ============================================================
CREATE TABLE medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    record_date DATE NOT NULL,
    height_cm DECIMAL(5,2),
    weight_kg DECIMAL(5,2),
    blood_pressure VARCHAR(20),
    pulse_rate INT,
    temperature DECIMAL(4,1),
    vision_left VARCHAR(20),
    vision_right VARCHAR(20),
    allergies TEXT,
    chronic_conditions TEXT,
    past_illnesses TEXT,
    surgical_history TEXT,
    family_medical_history TEXT,
    vaccination_records TEXT,
    medical_notes TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- ============================================================
-- MODULE 2: CONSULTATION & TREATMENT LOGS
-- ============================================================
CREATE TABLE consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    visit_date DATETIME NOT NULL,
    chief_complaint TEXT NOT NULL,
    symptoms TEXT,
    diagnosis TEXT,
    treatment_given TEXT,
    prescription TEXT,
    vital_signs VARCHAR(255),
    follow_up_date DATE,
    referral TEXT,
    attending_staff INT,
    status ENUM('ongoing','completed','referred','follow-up') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (attending_staff) REFERENCES users(id)
);

-- ============================================================
-- MODULE 3: MEDICINE INVENTORY & DISPENSING
-- ============================================================
CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name VARCHAR(150) NOT NULL,
    generic_name VARCHAR(150),
    category VARCHAR(100),
    unit VARCHAR(50),
    quantity_in_stock INT DEFAULT 0,
    minimum_stock INT DEFAULT 10,
    expiry_date DATE,
    supplier VARCHAR(100),
    unit_cost DECIMAL(10,2) DEFAULT 0.00,
    description TEXT,
    status ENUM('available','low_stock','out_of_stock','expired') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE medicine_dispensing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    consultation_id INT,
    medicine_id INT NOT NULL,
    quantity_dispensed INT NOT NULL,
    dispense_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    dispensed_by INT,
    purpose TEXT,
    notes TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (consultation_id) REFERENCES consultations(id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(id),
    FOREIGN KEY (dispensed_by) REFERENCES users(id)
);

CREATE TABLE medicine_stock_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    action ENUM('restock','dispense','expired','adjustment') NOT NULL,
    quantity INT NOT NULL,
    quantity_before INT,
    quantity_after INT,
    reference_id INT,
    performed_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- ============================================================
-- MODULE 4: MEDICAL CLEARANCE ISSUANCE
-- ============================================================
CREATE TABLE medical_clearances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clearance_number VARCHAR(30) UNIQUE NOT NULL,
    student_id INT NOT NULL,
    purpose ENUM('enrollment','school_activity','sports','ojt','graduation','other') NOT NULL,
    other_purpose VARCHAR(255),
    issued_date DATE NOT NULL,
    valid_until DATE,
    status ENUM('pending','approved','rejected','expired') DEFAULT 'pending',
    medical_findings TEXT,
    remarks TEXT,
    issued_by INT,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- ============================================================
-- MODULE 5: HEALTH INCIDENT REPORTING
-- ============================================================
CREATE TABLE health_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_number VARCHAR(30) UNIQUE NOT NULL,
    student_id INT NOT NULL,
    incident_date DATETIME NOT NULL,
    incident_type ENUM('accident','injury','illness','emergency','fainting','allergic_reaction','other') NOT NULL,
    location VARCHAR(255),
    description TEXT NOT NULL,
    immediate_action TEXT,
    treatment_given TEXT,
    referred_to VARCHAR(255),
    hospital_name VARCHAR(255),
    injury_severity ENUM('minor','moderate','severe','critical') DEFAULT 'minor',
    witnesses TEXT,
    status ENUM('open','resolved','referred','follow-up') DEFAULT 'open',
    reported_by INT,
    resolved_by INT,
    resolution_notes TEXT,
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (reported_by) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id)
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Default admin user (password: Admin@123)
INSERT INTO users (full_name, email, password, role) VALUES
('System Administrator', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Dr. Maria Santos', 'dr.santos@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor'),
('Nurse Ana Reyes', 'nurse.reyes@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nurse');

-- Sample students
INSERT INTO students (student_id, full_name, date_of_birth, gender, course, year_level, contact_number, email, blood_type) VALUES
('2021-0001', 'Juan Dela Cruz', '2002-03-15', 'Male', 'BS Computer Science', '3rd Year', '09171234567', 'juan.delacruz@student.edu', 'O+'),
('2021-0002', 'Maria Clara Santos', '2003-07-22', 'Female', 'BS Nursing', '2nd Year', '09281234567', 'maria.santos@student.edu', 'A+'),
('2022-0003', 'Pedro Reyes', '2001-11-08', 'Male', 'BS Engineering', '4th Year', '09391234567', 'pedro.reyes@student.edu', 'B+'),
('2022-0004', 'Ana Gonzales', '2003-05-30', 'Female', 'BA Psychology', '2nd Year', '09501234567', 'ana.gonzales@student.edu', 'AB-');

-- Sample medicines
INSERT INTO medicines (medicine_name, generic_name, category, unit, quantity_in_stock, minimum_stock, expiry_date, unit_cost) VALUES
('Biogesic', 'Paracetamol 500mg', 'Analgesic/Antipyretic', 'tablet', 200, 50, '2026-12-31', 2.50),
('Neozep', 'Chlorphenamine + Paracetamol', 'Antihistamine', 'tablet', 100, 30, '2026-06-30', 5.00),
('Lagundi', 'Vitex negundo 300mg', 'Cough Remedy', 'tablet', 80, 20, '2025-12-31', 3.00),
('Mefenamic Acid 500mg', 'Mefenamic Acid', 'NSAID', 'capsule', 150, 40, '2026-09-30', 4.50),
('Cetirizine 10mg', 'Cetirizine HCl', 'Antihistamine', 'tablet', 60, 20, '2026-03-31', 6.00),
('Povidone Iodine', 'Povidone Iodine 10%', 'Antiseptic', 'bottle', 15, 5, '2025-08-31', 45.00),
('Oral Rehydration Salt', 'ORS', 'Electrolyte Replenisher', 'sachet', 50, 15, '2026-12-31', 8.00);
