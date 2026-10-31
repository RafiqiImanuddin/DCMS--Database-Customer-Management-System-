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

CREATE TABLE IF NOT EXISTS cross_selling (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_period VARCHAR(7) NOT NULL,
  cabang VARCHAR(30),
  info VARCHAR(100),
  keterangan VARCHAR(100),
  group_name VARCHAR(150),
  posting_date DATE,
  waktu VARCHAR(20),
  document_number VARCHAR(40),
  customer_vendor_code VARCHAR(50),
  customer_vendor_name VARCHAR(200),
  item_code VARCHAR(100),
  description VARCHAR(255),
  item_group_name VARCHAR(100),
  brand VARCHAR(100),
  warehouse_code VARCHAR(100),
  quantity DECIMAL(18,4) DEFAULT 0,
  selling_price DECIMAL(18,2) DEFAULT 0,
  price_before_discount DECIMAL(18,2) DEFAULT 0,
  discount_difference DECIMAL(18,2) DEFAULT 0,
  p_bonus DECIMAL(18,2) DEFAULT 0,
  p_voucher DECIMAL(18,2) DEFAULT 0,
  p_nego DECIMAL(18,2) DEFAULT 0,
  p_leasing DECIMAL(18,2) DEFAULT 0,
  p_program DECIMAL(18,2) DEFAULT 0,
  p_edc DECIMAL(18,2) DEFAULT 0,
  p_authorization_posvit DECIMAL(18,2) DEFAULT 0,
  price_list VARCHAR(100),
  remarks TEXT,
  schl_code VARCHAR(100),
  sales_employee_name VARCHAR(150),
  promo VARCHAR(150),
  user_input VARCHAR(150),
  customer_phone VARCHAR(30),
  cost_price DECIMAL(18,2) DEFAULT 0,
  gross_profit DECIMAL(18,2) DEFAULT 0,
  reseller VARCHAR(150),
  leasing_subsidy DECIMAL(18,2) DEFAULT 0,
  customer_transaction VARCHAR(100),
  name VARCHAR(150),
  reference_number VARCHAR(100),
  purchase_order_number VARCHAR(100),
  source_file VARCHAR(255),
  source_row BIGINT UNSIGNED,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cross_selling_source (source_period, source_file, source_row),
  INDEX idx_cross_selling_period (source_period),
  INDEX idx_cross_selling_branch (cabang),
  INDEX idx_cross_selling_customer (customer_vendor_code),
  INDEX idx_cross_selling_document (document_number),
  INDEX idx_cross_selling_date (posting_date),
  INDEX idx_cross_selling_item (item_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
