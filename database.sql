-- Create database
CREATE DATABASE IF NOT EXISTS sales_erp;
USE sales_erp;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'salesman', 'delivery', 'manager') DEFAULT 'salesman',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO users (username, password, full_name, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com', 'admin');

-- Salesmen table
CREATE TABLE salesmen (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    salesman_code VARCHAR(20) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    commission_rate DECIMAL(5,2) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Delivery men table
CREATE TABLE delivery_men (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    delivery_code VARCHAR(20) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    salary_type ENUM('fixed', 'per_delivery') DEFAULT 'per_delivery',
    salary_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_code VARCHAR(50) UNIQUE,
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20),
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Daily sales table
CREATE TABLE daily_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    salesman_id INT NOT NULL,
    sale_date DATE NOT NULL,
    invoice_number VARCHAR(50) UNIQUE,
    customer_name VARCHAR(200),
    customer_phone VARCHAR(20),
    customer_address TEXT,
    total_amount DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    net_amount DECIMAL(10,2) DEFAULT 0,
    payment_method ENUM('cash', 'card', 'bank_transfer') DEFAULT 'cash',
    payment_status ENUM('paid', 'pending', 'partial') DEFAULT 'pending',
    notes TEXT,
    attachment_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salesman_id) REFERENCES salesmen(id) ON DELETE CASCADE
);

-- Sales items table
CREATE TABLE sales_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES daily_sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Delivery payments table
CREATE TABLE delivery_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    delivery_man_id INT NOT NULL,
    sale_id INT,
    payment_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('salary', 'delivery_charge', 'advance') DEFAULT 'delivery_charge',
    notes TEXT,
    attachment_path VARCHAR(500),
    status ENUM('paid', 'pending') DEFAULT 'paid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (delivery_man_id) REFERENCES delivery_men(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_id) REFERENCES daily_sales(id) ON DELETE SET NULL
);

-- Bank deposits table
CREATE TABLE bank_deposits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    deposit_date DATE NOT NULL,
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    amount DECIMAL(10,2) NOT NULL,
    deposit_slip_number VARCHAR(50),
    sale_id INT,
    description TEXT,
    attachment_path VARCHAR(500),
    status ENUM('completed', 'pending') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES daily_sales(id) ON DELETE SET NULL
);

-- Reports table
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_type VARCHAR(50),
    report_date DATE,
    generated_by INT,
    file_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'Administrator', 'admin@example.com', 'admin');

-- Insert sample products
INSERT INTO products (product_code, product_name, price, unit, stock_quantity) VALUES
('PROD001', 'Product A', 100.00, 'pcs', 100),
('PROD002', 'Product B', 150.00, 'pcs', 150),
('PROD003', 'Product C', 200.00, 'box', 75);