CREATE DATABASE IF NOT EXISTS financial_system;
USE financial_system;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    full_name VARCHAR(100),
    role ENUM('admin','owner','manager') DEFAULT 'owner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Daily entries table
CREATE TABLE daily_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entry_date DATE NOT NULL,
    sales DECIMAL(10,2) DEFAULT 0,
    deposit DECIMAL(10,2) DEFAULT 0,
    expense DECIMAL(10,2) DEFAULT 0,
    short_deposit DECIMAL(10,2) DEFAULT 0,
    owner_count INT DEFAULT 0,
    paid_clip_status ENUM('paid','pending') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Monthly reports table
CREATE TABLE monthly_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_month DATE NOT NULL,
    bank_statement_file VARCHAR(255),
    salary_slip_file VARCHAR(255),
    loan_deduction_file VARCHAR(255),
    office_paid_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attachments table
CREATE TABLE attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_type ENUM('daily','monthly','owner','expense') NOT NULL,
    reference_id INT NOT NULL,
    file_name VARCHAR(255),
    file_path VARCHAR(500),
    file_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Automation transactions table
CREATE TABLE automation_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_date DATE NOT NULL,
    type ENUM('credit','debit') NOT NULL,
    category VARCHAR(100),
    amount DECIMAL(10,2),
    description TEXT,
    is_automated BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Owners table
CREATE TABLE owners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    role VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

INSERT INTO owners (name, role) VALUES
('John Doe', 'Managing Owner'),
('Jane Smith', 'Co-Owner'),
('Mike Lee', 'Partner'),
('Sarah Chen', 'Investor');