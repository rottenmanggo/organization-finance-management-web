
CREATE DATABASE IF NOT EXISTS orgfinance;
USE orgfinance;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    username VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255),
    role ENUM('KEUANGAN','VIEWER') NOT NULL
);

CREATE TABLE periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT 1
);

CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_id INT,
    name VARCHAR(100),
    nim VARCHAR(50),
    division VARCHAR(100),
    contact VARCHAR(100),
    is_active BOOLEAN DEFAULT 1,
    INDEX(period_id)
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('IN','OUT'),
    name VARCHAR(100)
);

CREATE TABLE payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_id INT,
    name VARCHAR(100),
    pic VARCHAR(100),
    start_date DATE,
    end_date DATE,
    status VARCHAR(50),
    description TEXT,
    INDEX(period_id)
);

CREATE TABLE budget_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT,
    category_id INT,
    name VARCHAR(100),
    qty INT,
    unit VARCHAR(50),
    unit_price DECIMAL(15,2),
    subtotal DECIMAL(15,2)
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_id INT,
    trx_no VARCHAR(50),
    date DATE,
    type ENUM('IN','OUT'),
    category_id INT,
    amount DECIMAL(15,2),
    method_id INT,
    description TEXT,
    program_id INT NULL,
    attachment_path VARCHAR(255),
    INDEX(period_id),
    INDEX(date)
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    entity VARCHAR(100),
    entity_id INT,
    detail TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
