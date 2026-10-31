CREATE DATABASE IF NOT EXISTS dcms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dcms;

CREATE TABLE IF NOT EXISTS customers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_code VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150),
  address TEXT,
  brand VARCHAR(100),
  model VARCHAR(100),
  sku VARCHAR(100),
  purchase_date DATE,
  unit_price DECIMAL(14,2) DEFAULT 0,
  status ENUM('repeat','upgrade','reactivate','prospect') DEFAULT 'prospect',
  last_contact_at DATETIME NULL,
  next_follow_up_at DATETIME NULL,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  transaction_number VARCHAR(40) NOT NULL UNIQUE,
  transaction_date DATE NOT NULL,
  sku VARCHAR(100),
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS activities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  activity_type VARCHAR(50) NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  scheduled_at DATETIME NULL,
  completed_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

INSERT INTO customers (customer_code,name,phone,email,brand,model,sku,purchase_date,unit_price,status,last_contact_at,next_follow_up_at) VALUES
('CUS-0001','Andi Pratama','081234567891','andi@example.com','Samsung','Galaxy S24','S24-BLK-256','2026-09-18',12999000,'repeat','2026-10-28 09:00:00','2026-11-03 10:00:00'),
('CUS-0002','Dewi Lestari','081234567892','dewi@example.com','Apple','iPhone 15 Pro','IP15P-NAT-256','2026-08-05',18999000,'upgrade','2026-10-25 14:30:00','2026-11-01 09:30:00'),
('CUS-0003','Budi Santoso','081234567893','budi@example.com','Xiaomi','Redmi Note 13','RN13-BLU-128','2026-02-11',2899000,'reactivate','2026-09-20 11:00:00','2026-10-31 13:00:00'),
('CUS-0004','Siti Rahma','081234567894','siti@example.com','Samsung','Galaxy A55','A55-LAV-256','2026-10-20',5999000,'prospect','2026-10-29 16:00:00','2026-11-02 11:00:00')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO transactions (customer_id,transaction_number,transaction_date,sku,quantity,unit_price,total_amount)
SELECT id,'TRX-0001','2026-09-18',sku,1,unit_price,unit_price FROM customers WHERE customer_code='CUS-0001'
ON DUPLICATE KEY UPDATE transaction_number=transaction_number;
