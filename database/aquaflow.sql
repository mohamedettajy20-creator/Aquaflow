-- =====================================================================================
--  AquaFlow — Smart Water Management System
--  Full MySQL Database Schema
--  Engine: InnoDB | Charset: utf8mb4
--  PFE Project
-- =====================================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS aquaflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aquaflow;

-- =====================================================================================
-- 1. USERS — base authentication table shared by all roles (admin / agent / customer)
-- =====================================================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(120)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,
    role            ENUM('admin','agent','customer') NOT NULL,
    phone           VARCHAR(30)     DEFAULT NULL,
    avatar          VARCHAR(255)    DEFAULT NULL,
    status          ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    remember_token  VARCHAR(100)    DEFAULT NULL,
    last_login_at   DATETIME        DEFAULT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

-- =====================================================================================
-- 2. ADMINS — extra profile data for administrator users
-- =====================================================================================
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    job_title       VARCHAR(100) DEFAULT 'System Administrator',
    permissions     JSON DEFAULT NULL COMMENT 'granular permission overrides',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admins_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================================
-- 3. AGENTS — field agents who capture meter readings
-- =====================================================================================
DROP TABLE IF EXISTS agents;
CREATE TABLE agents (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    employee_code   VARCHAR(30)  NOT NULL UNIQUE,
    zone            VARCHAR(100) DEFAULT NULL COMMENT 'assigned geographic zone / sector',
    hire_date       DATE DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_agents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================================
-- 4. CUSTOMERS — subscriber profile data
-- =====================================================================================
DROP TABLE IF EXISTS customers;
CREATE TABLE customers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    customer_code   VARCHAR(30)  NOT NULL UNIQUE,
    address         VARCHAR(255) NOT NULL,
    city            VARCHAR(100) DEFAULT NULL,
    postal_code     VARCHAR(20)  DEFAULT NULL,
    customer_type   ENUM('residential','commercial','industrial') NOT NULL DEFAULT 'residential',
    agent_id        INT UNSIGNED DEFAULT NULL COMMENT 'assigned field agent',
    registered_at   DATE NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_customers_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    CONSTRAINT fk_customers_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL,
    INDEX idx_customers_city (city)
) ENGINE=InnoDB;

-- =====================================================================================
-- 5. METERS — one meter per customer (a customer could have several)
-- =====================================================================================
DROP TABLE IF EXISTS meters;
CREATE TABLE meters (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meter_number        VARCHAR(40) NOT NULL UNIQUE,
    customer_id         INT UNSIGNED NOT NULL,
    installation_date   DATE NOT NULL,
    status              ENUM('active','inactive','maintenance','replaced') NOT NULL DEFAULT 'active',
    initial_reading     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    location_note       VARCHAR(255) DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_meters_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_meters_status (status)
) ENGINE=InnoDB;

-- =====================================================================================
-- 6. READINGS — monthly meter readings captured by an agent
-- =====================================================================================
DROP TABLE IF EXISTS readings;
CREATE TABLE readings (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meter_id            INT UNSIGNED NOT NULL,
    agent_id            INT UNSIGNED DEFAULT NULL,
    previous_reading    DECIMAL(12,2) NOT NULL,
    current_reading     DECIMAL(12,2) NOT NULL,
    consumption         DECIMAL(12,2) GENERATED ALWAYS AS (current_reading - previous_reading) STORED,
    reading_date         DATE NOT NULL,
    photo_path           VARCHAR(255) DEFAULT NULL,
    status                ENUM('pending','validated','rejected') NOT NULL DEFAULT 'pending',
    period_month          TINYINT UNSIGNED NOT NULL,
    period_year           SMALLINT UNSIGNED NOT NULL,
    notes                 VARCHAR(255) DEFAULT NULL,
    created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_readings_meter FOREIGN KEY (meter_id) REFERENCES meters(id) ON DELETE CASCADE,
    CONSTRAINT fk_readings_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL,
    CONSTRAINT chk_reading_positive CHECK (current_reading >= previous_reading),
    UNIQUE KEY uniq_meter_period (meter_id, period_year, period_month),
    INDEX idx_readings_status (status)
) ENGINE=InnoDB;

-- =====================================================================================
-- 7. SETTINGS — system settings incl. tier pricing table (key/value + JSON)
-- =====================================================================================
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key     VARCHAR(100) NOT NULL UNIQUE,
    setting_value   TEXT NOT NULL,
    setting_group   VARCHAR(50) DEFAULT 'general',
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================================
-- 8. INVOICES — monthly bills generated from readings
-- =====================================================================================
DROP TABLE IF EXISTS invoices;
CREATE TABLE invoices (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number  VARCHAR(30) NOT NULL UNIQUE,
    customer_id     INT UNSIGNED NOT NULL,
    meter_id        INT UNSIGNED NOT NULL,
    reading_id      INT UNSIGNED DEFAULT NULL,
    consumption     DECIMAL(12,2) NOT NULL,
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_rate        DECIMAL(5,2)  NOT NULL DEFAULT 7.00 COMMENT 'percentage',
    tax_amount      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_amount    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    issue_date      DATE NOT NULL,
    due_date        DATE NOT NULL,
    status          ENUM('unpaid','paid','late','cancelled') NOT NULL DEFAULT 'unpaid',
    period_month    TINYINT UNSIGNED NOT NULL,
    period_year     SMALLINT UNSIGNED NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoices_meter    FOREIGN KEY (meter_id)    REFERENCES meters(id)    ON DELETE CASCADE,
    CONSTRAINT fk_invoices_reading  FOREIGN KEY (reading_id)  REFERENCES readings(id)  ON DELETE SET NULL,
    UNIQUE KEY uniq_customer_period (customer_id, period_year, period_month),
    INDEX idx_invoices_status (status)
) ENGINE=InnoDB;

-- =====================================================================================
-- 9. INVOICE_DETAILS — tier pricing breakdown lines for each invoice
-- =====================================================================================
DROP TABLE IF EXISTS invoice_details;
CREATE TABLE invoice_details (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id      INT UNSIGNED NOT NULL,
    tier_label      VARCHAR(60) NOT NULL COMMENT 'e.g. "Tier 1 (0-10 m3)"',
    tier_from       DECIMAL(12,2) NOT NULL,
    tier_to         DECIMAL(12,2) DEFAULT NULL,
    volume_m3       DECIMAL(12,2) NOT NULL,
    unit_price      DECIMAL(10,4) NOT NULL,
    line_total      DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_invdetails_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================================
-- 10. PAYMENTS — payment history against invoices
-- =====================================================================================
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id      INT UNSIGNED NOT NULL,
    customer_id     INT UNSIGNED NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    method          ENUM('cash','card','bank_transfer','mobile_payment') NOT NULL DEFAULT 'cash',
    reference       VARCHAR(80) DEFAULT NULL,
    paid_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    recorded_by     INT UNSIGNED DEFAULT NULL COMMENT 'admin/agent user id who recorded it',
    status          ENUM('completed','pending','failed','refunded') NOT NULL DEFAULT 'completed',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_invoice  FOREIGN KEY (invoice_id)  REFERENCES invoices(id)  ON DELETE CASCADE,
    CONSTRAINT fk_payments_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    CONSTRAINT fk_payments_user     FOREIGN KEY (recorded_by) REFERENCES users(id)     ON DELETE SET NULL,
    INDEX idx_payments_status (status)
) ENGINE=InnoDB;

-- =====================================================================================
-- 11. NOTIFICATIONS — in-app alerts per user
-- =====================================================================================
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    type            ENUM('new_invoice','reading_completed','payment_reminder','late_payment','system') NOT NULL,
    title           VARCHAR(150) NOT NULL,
    message         VARCHAR(500) NOT NULL,
    link            VARCHAR(255) DEFAULT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_unread (user_id, is_read)
) ENGINE=InnoDB;

-- =====================================================================================
-- 12. ACTIVITY_LOGS — audit trail of key actions (extra feature)
-- =====================================================================================
DROP TABLE IF EXISTS activity_logs;
CREATE TABLE activity_logs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED DEFAULT NULL,
    action           VARCHAR(150) NOT NULL,
    description      VARCHAR(500) DEFAULT NULL,
    ip_address       VARCHAR(45) DEFAULT NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================================
--  SAMPLE / SEED DATA
--  Default password for every seeded account is:  Passw0rd!
-- =====================================================================================

-- Password hash for "Passw0rd!" (bcrypt) — generated & verified with PHP password_hash()/password_verify()
-- $2y$10$xS14jgEDFVjNkDbbWRZa8.3s/HsSY7xT1NZkd0MVN0Csvs.AV9Zkq
INSERT INTO users (full_name, email, password_hash, role, phone, status) VALUES
('Mohammed Alaoui', 'admin@aquaflow.local', '$2y$10$xS14jgEDFVjNkDbbWRZa8.3s/HsSY7xT1NZkd0MVN0Csvs.AV9Zkq', 'admin', '0600000001', 'active'),
('Sara Idrissi',     'sara.agent@aquaflow.local', '$2y$10$xS14jgEDFVjNkDbbWRZa8.3s/HsSY7xT1NZkd0MVN0Csvs.AV9Zkq', 'agent', '0600000002', 'active'),
('Youssef Bennani',  'youssef.agent@aquaflow.local', '$2y$10$xS14jgEDFVjNkDbbWRZa8.3s/HsSY7xT1NZkd0MVN0Csvs.AV9Zkq', 'agent', '0600000003', 'active'),
('Fatima Zahra',     'fatima@example.com', '$2y$10$xS14jgEDFVjNkDbbWRZa8.3s/HsSY7xT1NZkd0MVN0Csvs.AV9Zkq', 'customer', '0611111111', 'active'),
('Karim Tazi',       'karim@example.com',  '$2y$10$xS14jgEDFVjNkDbbWRZa8.3s/HsSY7xT1NZkd0MVN0Csvs.AV9Zkq', 'customer', '0622222222', 'active'),
('Laila Amrani',     'laila@example.com',  '$2y$10$xS14jgEDFVjNkDbbWRZa8.3s/HsSY7xT1NZkd0MVN0Csvs.AV9Zkq', 'customer', '0633333333', 'active'),
('Omar Chraibi',     'omar@example.com',   '$2y$10$xS14jgEDFVjNkDbbWRZa8.3s/HsSY7xT1NZkd0MVN0Csvs.AV9Zkq', 'customer', '0644444444', 'active');

INSERT INTO admins (user_id, job_title) VALUES (1, 'System Administrator');

INSERT INTO agents (user_id, employee_code, zone, hire_date) VALUES
(2, 'AG-001', 'Marrakech Centre', '2023-02-01'),
(3, 'AG-002', 'Gueliz', '2023-06-15');

INSERT INTO customers (user_id, customer_code, address, city, postal_code, customer_type, agent_id, registered_at) VALUES
(4, 'CUS-1001', '12 Rue Ibn Sina, Guéliz', 'Marrakesh', '40000', 'residential', 1, '2024-01-10'),
(5, 'CUS-1002', '45 Avenue Mohammed V',    'Marrakesh', '40000', 'residential', 1, '2024-02-15'),
(6, 'CUS-1003', '8 Lotissement Al Amal',   'Marrakesh', '40010', 'commercial',  2, '2024-03-05'),
(7, 'CUS-1004', '19 Rue Yougoslavie',      'Marrakesh', '40000', 'residential', 2, '2024-04-20');

INSERT INTO meters (meter_number, customer_id, installation_date, status, initial_reading) VALUES
('MTR-0001', 1, '2024-01-10', 'active', 0.00),
('MTR-0002', 2, '2024-02-15', 'active', 0.00),
('MTR-0003', 3, '2024-03-05', 'active', 120.00),
('MTR-0004', 4, '2024-04-20', 'active', 0.00);

INSERT INTO readings (meter_id, agent_id, previous_reading, current_reading, reading_date, status, period_month, period_year) VALUES
(1, 1, 0.00,   14.50, '2025-05-28', 'validated', 5, 2025),
(1, 1, 14.50,  29.80, '2025-06-28', 'validated', 6, 2025),
(2, 1, 0.00,   22.00, '2025-05-27', 'validated', 5, 2025),
(2, 1, 22.00,  40.30, '2025-06-27', 'validated', 6, 2025),
(3, 2, 120.00, 165.00,'2025-05-30', 'validated', 5, 2025),
(4, 2, 0.00,   9.80,  '2025-06-25', 'validated', 6, 2025);

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('company_name', 'AquaFlow', 'general'),
('company_address', 'Marrakesh, Morocco', 'general'),
('currency', 'MAD', 'billing'),
('tax_rate', '7.00', 'billing'),
('invoice_due_days', '15', 'billing'),
('tier_pricing', '[{"from":0,"to":10,"price":3.50},{"from":10,"to":30,"price":6.00},{"from":30,"to":60,"price":9.50},{"from":60,"to":null,"price":13.00}]', 'billing'),
('late_fee_percent', '5.00', 'billing');

INSERT INTO invoices (invoice_number, customer_id, meter_id, reading_id, consumption, subtotal, tax_rate, tax_amount, total_amount, issue_date, due_date, status, period_month, period_year) VALUES
('INV-2025-05-1001', 1, 1, 1, 14.50, 50.75, 7.00, 3.55, 54.30, '2025-06-01', '2025-06-16', 'paid',   5, 2025),
('INV-2025-06-1001', 1, 1, 2, 15.30, 53.55, 7.00, 3.75, 57.30, '2025-07-01', '2025-07-16', 'unpaid', 6, 2025),
('INV-2025-05-1002', 2, 2, 3, 22.00, 91.00, 7.00, 6.37, 97.37, '2025-06-01', '2025-06-16', 'paid',   5, 2025),
('INV-2025-06-1002', 2, 2, 4, 18.30, 76.95, 7.00, 5.39, 82.34, '2025-07-01', '2025-07-16', 'late',   6, 2025),
('INV-2025-05-1003', 3, 3, 5, 45.00, 292.50,7.00, 20.48,312.98, '2025-06-01', '2025-06-16', 'unpaid', 5, 2025);

INSERT INTO payments (invoice_id, customer_id, amount, method, reference, recorded_by, status) VALUES
(1, 1, 54.30, 'card', 'TXN-88231', 1, 'completed'),
(3, 2, 97.37, 'cash', NULL, 1, 'completed');

INSERT INTO notifications (user_id, type, title, message, is_read) VALUES
(4, 'new_invoice', 'New invoice available', 'Your invoice INV-2025-06-1001 of 57.30 MAD is ready.', 0),
(5, 'late_payment', 'Payment overdue', 'Invoice INV-2025-06-1002 is now late. Please settle it to avoid service interruption.', 0),
(1, 'reading_completed', 'Reading captured', 'Agent Sara Idrissi captured a new reading for meter MTR-0004.', 1);
