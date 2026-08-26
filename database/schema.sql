-- =====================================================================
--  CargoFlow — Logistics & Shipment Tracking Platform
--  MySQL Database Schema + Seed Data
--  Import this file via phpMyAdmin (cPanel) or:  mysql -u user -p db < schema.sql
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Users (admin / staff accounts)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `username` VARCHAR(60) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Customers
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_code` VARCHAR(30) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) DEFAULT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `company` VARCHAR(160) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `notes` TEXT,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_code` (`customer_code`),
  KEY `idx_customers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Drivers
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `drivers`;
CREATE TABLE `drivers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) DEFAULT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `license_number` VARCHAR(80) DEFAULT NULL,
  `vehicle_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('available','on_delivery','off_duty') NOT NULL DEFAULT 'available',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_drivers_vehicle` (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Vehicles
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `type` ENUM('truck','van','bike','ship','plane') NOT NULL DEFAULT 'truck',
  `plate_number` VARCHAR(60) DEFAULT NULL,
  `capacity` VARCHAR(80) DEFAULT NULL,
  `driver_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('available','in_transit','maintenance') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vehicles_driver` (`driver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Shipments
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `shipments`;
CREATE TABLE `shipments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tracking_number` VARCHAR(40) NOT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `origin` VARCHAR(255) DEFAULT NULL,
  `destination` VARCHAR(255) DEFAULT NULL,
  `origin_address` TEXT,
  `destination_address` TEXT,
  `service_type` ENUM('standard','express','overnight','freight','air','sea') NOT NULL DEFAULT 'standard',
  `package_type` ENUM('document','parcel','pallet','container') NOT NULL DEFAULT 'parcel',
  `package_image` VARCHAR(255) DEFAULT NULL,
  `weight` DECIMAL(10,2) DEFAULT NULL,
  `dimensions` VARCHAR(60) DEFAULT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `description` TEXT,
  `carrier` VARCHAR(120) DEFAULT NULL,
  `driver_id` INT UNSIGNED DEFAULT NULL,
  `vehicle_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('pending','picked_up','in_transit','out_for_delivery','delivered','on_hold','customs','cancelled','returned') NOT NULL DEFAULT 'pending',
  `current_location` VARCHAR(255) DEFAULT NULL,
  `estimated_delivery` DATE DEFAULT NULL,
  `shipped_at` DATETIME DEFAULT NULL,
  `delivered_at` DATETIME DEFAULT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `notes` TEXT,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shipments_tracking` (`tracking_number`),
  KEY `idx_shipments_customer` (`customer_id`),
  KEY `idx_shipments_status` (`status`),
  KEY `idx_shipments_driver` (`driver_id`),
  KEY `idx_shipments_vehicle` (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tracking events (status / event history)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tracking_events`;
CREATE TABLE `tracking_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `shipment_id` INT UNSIGNED NOT NULL,
  `status` VARCHAR(40) NOT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `event_time` DATETIME NOT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_events_shipment` (`shipment_id`),
  CONSTRAINT `fk_events_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Invoices
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(40) NOT NULL,
  `shipment_id` INT UNSIGNED DEFAULT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('draft','unpaid','paid','overdue','cancelled') NOT NULL DEFAULT 'unpaid',
  `issue_date` DATE DEFAULT NULL,
  `due_date` DATE DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoices_number` (`invoice_number`),
  KEY `idx_invoices_customer` (`customer_id`),
  KEY `idx_invoices_shipment` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Payments
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED DEFAULT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `method` ENUM('cash','card','bank','paypal','transfer') NOT NULL DEFAULT 'card',
  `transaction_id` VARCHAR(120) DEFAULT NULL,
  `status` ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
  `payment_date` DATETIME DEFAULT NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_invoice` (`invoice_id`),
  KEY `idx_payments_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Notifications
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(160) NOT NULL,
  `message` TEXT,
  `type` ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Quotes (Get a Quote requests)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `quotes`;
CREATE TABLE `quotes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) DEFAULT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `origin` VARCHAR(255) DEFAULT NULL,
  `destination` VARCHAR(255) DEFAULT NULL,
  `service_type` VARCHAR(50) DEFAULT NULL,
  `package_type` VARCHAR(50) DEFAULT NULL,
  `weight` VARCHAR(40) DEFAULT NULL,
  `message` TEXT,
  `estimated_price` DECIMAL(12,2) DEFAULT NULL,
  `status` ENUM('new','reviewed','converted','declined') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_quotes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Contact messages
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) DEFAULT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `message` TEXT,
  `status` ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Settings (key/value store)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(120) NOT NULL,
  `setting_value` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SEED DATA
-- =====================================================================

-- Admin user (password: admin123)
INSERT INTO `users` (`id`,`name`,`email`,`username`,`password`,`role`,`status`) VALUES
(1,'CargoFlow Admin','admin@cargoflow.test','admin','$2y$10$Qwt/Q6QO6Nt8vy1tlXRPM.qGP6Gm10SIz11u9RAyXXcY6u9hkNte2','admin','active'),
(2,'Operations Manager','manager@cargoflow.test','manager','$2y$10$Qwt/Q6QO6Nt8vy1tlXRPM.qGP6Gm10SIz11u9RAyXXcY6u9hkNte2','manager','active');

INSERT INTO `customers` (`id`,`customer_code`,`name`,`email`,`phone`,`company`,`address`,`city`,`state`,`country`,`postal_code`,`status`) VALUES
(1,'CUS-0001','Alexandra Reyes','alexandra@northwind.com','+1 415 555 0134','Northwind Traders','425 Market Street','San Francisco','CA','United States','94105','active'),
(2,'CUS-0002','Marcus Chen','marcus@atlastech.io','+1 206 555 0177','Atlas Technologies','1200 4th Avenue','Seattle','WA','United States','98101','active'),
(3,'CUS-0003','Sofia Bianchi','sofia@bellaitalia.it','+39 02 555 0192','Bella Italia Imports','Via Torino 45','Milan','Lombardy','Italy','20123','active'),
(4,'CUS-0004','Daniel Okafor','daniel@savannah.ng','+234 1 555 0166','Savannah Retail Group','14 Marina Road','Lagos','Lagos','Nigeria','100001','active'),
(5,'CUS-0005','Emma Lindqvist','emma@nordicgoods.se','+46 8 555 0128','Nordic Goods AB','Kungsgatan 18','Stockholm','Stockholm','Sweden','11135','inactive');

INSERT INTO `drivers` (`id`,`name`,`email`,`phone`,`license_number`,`status`) VALUES
(1,'James Carter','james.carter@cargoflow.test','+1 415 555 0101','DL-8845120','available'),
(2,'Luis Romero','luis.romero@cargoflow.test','+1 213 555 0182','DL-3328197','on_delivery'),
(3,'Aisha Bello','aisha.bello@cargoflow.test','+44 20 555 0144','UK-DL-091283','available'),
(4,'Tomás Silva','tomas.silva@cargoflow.test','+351 21 555 0195','PT-DL-774102','off_duty'),
(5,'Mei Lin','mei.lin@cargoflow.test','+65 6 555 0130','SG-DL-220914','available');

INSERT INTO `vehicles` (`id`,`name`,`type`,`plate_number`,`capacity`,`driver_id`,`status`) VALUES
(1,'Mercedes Sprinter #1','van','CF-1024','3.5 tons',1,'available'),
(2,'Freightliner Cascadia','truck','CF-2088','26 tons',2,'in_transit'),
(3,'Ford Transit Box','van','CF-3310','4.5 tons',3,'available'),
(4,'Volvo FH16','truck','CF-4475','44 tons',NULL,'available'),
(5,'Cargo Bike Courier','bike','CF-5001','60 kg',NULL,'available'),
(6,'Boeing 747 Freighter','plane','CF-AIR-1','100 tons',NULL,'available');

UPDATE `drivers` SET `vehicle_id` = 1 WHERE id = 1;
UPDATE `drivers` SET `vehicle_id` = 2 WHERE id = 2;
UPDATE `drivers` SET `vehicle_id` = 3 WHERE id = 3;

INSERT INTO `shipments`
(`id`,`tracking_number`,`customer_id`,`origin`,`destination`,`origin_address`,`destination_address`,`service_type`,`package_type`,`weight`,`dimensions`,`quantity`,`description`,`carrier`,`driver_id`,`vehicle_id`,`status`,`current_location`,`estimated_delivery`,`shipped_at`,`delivered_at`,`price`,`currency`) VALUES
(1,'CF-8K4T9W2M7Q',1,'San Francisco, CA','Seattle, WA','425 Market Street, San Francisco, CA','1200 4th Avenue, Seattle, WA','standard','parcel',18.50,'40x30x20 cm',3,'Electronics components — fragile','CargoFlow Ground',2,2,'in_transit','Portland, OR','2026-08-29','2026-08-24 09:15:00',NULL,146.00,'USD'),
(2,'CF-3H7N2X9R4P',2,'Seattle, WA','New York, NY','1200 4th Avenue, Seattle, WA','350 5th Avenue, New York, NY','express','parcel',5.20,'30x20x10 cm',1,'Server spare parts','CargoFlow Air',NULL,6,'picked_up','Seattle, WA','2026-08-27','2026-08-25 14:40:00',NULL,289.50,'USD'),
(3,'CF-6M2W5K8T3J',3,'Milan, Italy','Frankfurt, Germany','Via Torino 45, Milan','Mainzer Landstraße 120, Frankfurt','freight','pallet',720.00,'120x100x120 cm',4,'Import pallet — textiles','CargoFlow Freight',3,3,'out_for_delivery','Frankfurt, Germany','2026-08-26','2026-08-22 08:00:00',NULL,1240.00,'EUR'),
(4,'CF-1Q9Z4X7C5V',4,'Lagos, Nigeria','Accra, Ghana','14 Marina Road, Lagos','Independence Avenue, Accra','sea','container',18500.00,'20 ft container',1,'Container of consumer goods','CargoFlow Sea',NULL,NULL,'on_hold','Lagos Port, Nigeria','2026-09-04','2026-08-18 11:30:00',NULL,3850.00,'USD'),
(5,'CF-5B8V2N6M3K',1,'San Francisco, CA','Los Angeles, CA','425 Market Street, San Francisco','700 Flower Street, Los Angeles','overnight','document',0.80,'A4 envelope',1,'Signed contracts','CargoFlow Express',1,1,'delivered','Los Angeles, CA','2026-08-23','2026-08-22 19:05:00','2026-08-23 09:22:00',62.00,'USD'),
(6,'CF-7R4T1Y9U6H',5,'Stockholm, Sweden','Copenhagen, Denmark','Kungsgatan 18, Stockholm','Rådhuspladsen 16, Copenhagen','standard','parcel',12.40,'50x40x25 cm',2,'Home goods samples','CargoFlow Ground',4,4,'pending','Stockholm, Sweden','2026-09-01',NULL,NULL,98.00,'EUR'),
(7,'CF-2N6K3J8H5G',2,'Seattle, WA','Portland, OR','1200 4th Avenue, Seattle','1221 SW 4th Avenue, Portland','express','parcel',9.80,'45x35x20 cm',1,'Urgent documents','CargoFlow Express',5,5,'cancelled','Seattle, WA','2026-08-25',NULL,NULL,84.00,'USD'),
(8,'CF-9W4E7R2T8Y',3,'Milan, Italy','Paris, France','Via Torino 45, Milan','1 Rue de Rivoli, Paris','air','parcel',24.60,'60x40x30 cm',2,'Fashion samples','CargoFlow Air',NULL,6,'in_transit','Zurich, Switzerland','2026-08-28','2026-08-25 06:10:00',NULL,312.00,'EUR');

-- Tracking events
INSERT INTO `tracking_events` (`shipment_id`,`status`,`location`,`description`,`event_time`,`created_by`) VALUES
(1,'pending','San Francisco, CA','Shipment information received','2026-08-24 09:15:00',1),
(1,'picked_up','San Francisco, CA','Package picked up by CargoFlow Ground','2026-08-24 15:42:00',1),
(1,'in_transit','Sacramento, CA','Departed Sacramento distribution hub','2026-08-25 04:30:00',1),
(1,'in_transit','Portland, OR','Arrived at Portland sorting facility','2026-08-26 02:18:00',1),
(2,'pending','Seattle, WA','Shipment information received','2026-08-25 14:40:00',1),
(2,'picked_up','Seattle, WA','Package picked up by CargoFlow Air','2026-08-25 16:55:00',1),
(3,'pending','Milan, Italy','Shipment information received','2026-08-22 08:00:00',1),
(3,'picked_up','Milan, Italy','Pallet loaded at origin warehouse','2026-08-22 12:20:00',1),
(3,'in_transit','Munich, Germany','Customs cleared, transiting Germany','2026-08-24 22:05:00',1),
(3,'out_for_delivery','Frankfurt, Germany','Out for delivery with local courier','2026-08-26 08:35:00',1),
(4,'pending','Lagos, Nigeria','Shipment information received','2026-08-18 11:30:00',1),
(4,'picked_up','Lagos, Nigeria','Container loaded at Lagos Port','2026-08-19 07:00:00',1),
(4,'on_hold','Lagos Port, Nigeria','Awaiting vessel departure (weather delay)','2026-08-21 13:45:00',1),
(5,'pending','San Francisco, CA','Shipment information received','2026-08-22 19:05:00',1),
(5,'picked_up','San Francisco, CA','Envelope collected by courier','2026-08-22 20:10:00',1),
(5,'in_transit','San Jose, CA','In transit via express line','2026-08-23 02:40:00',1),
(5,'out_for_delivery','Los Angeles, CA','Out for delivery','2026-08-23 08:15:00',1),
(5,'delivered','Los Angeles, CA','Delivered — signed by D. Okafor','2026-08-23 09:22:00',1),
(6,'pending','Stockholm, Sweden','Shipment information received','2026-08-25 10:00:00',1),
(8,'pending','Milan, Italy','Shipment information received','2026-08-25 06:10:00',1),
(8,'picked_up','Milan, Italy','Parcel collected by CargoFlow Air','2026-08-25 08:30:00',1),
(8,'in_transit','Zurich, Switzerland','Transiting Zurich air hub','2026-08-26 01:12:00',1);

INSERT INTO `invoices` (`id`,`invoice_number`,`shipment_id`,`customer_id`,`amount`,`tax`,`total`,`status`,`issue_date`,`due_date`,`paid_at`) VALUES
(1,'INV-2026-0001',1,1,146.00,12.41,158.41,'unpaid','2026-08-24','2026-09-07',NULL),
(2,'INV-2026-0002',2,2,289.50,24.61,314.11,'paid','2026-08-25','2026-09-08','2026-08-25 17:20:00'),
(3,'INV-2026-0003',3,3,1240.00,105.40,1345.40,'unpaid','2026-08-22','2026-09-05',NULL),
(4,'INV-2026-0004',5,1,62.00,5.27,67.27,'paid','2026-08-22','2026-09-05','2026-08-23 09:40:00'),
(5,'INV-2026-0005',8,3,312.00,26.52,338.52,'overdue','2026-08-25','2026-08-26',NULL);

INSERT INTO `payments` (`id`,`invoice_id`,`customer_id`,`amount`,`method`,`transaction_id`,`status`,`payment_date`,`notes`) VALUES
(1,2,2,314.11,'card','TXN-88A2K9','completed','2026-08-25 17:20:00','Card payment — Visa ****4242'),
(2,4,1,67.27,'paypal','TXN-77B3L1','completed','2026-08-23 09:40:00','PayPal payment');

INSERT INTO `notifications` (`id`,`user_id`,`title`,`message`,`type`,`link`,`is_read`) VALUES
(1,NULL,'New shipment created','Shipment CF-8K4T9W2M7Q has been created and assigned.','info','admin/shipments.php',1),
(2,NULL,'Payment received','Payment of $314.11 received for invoice INV-2026-0002.','success','admin/payments.php',0),
(3,NULL,'Shipment delayed','Shipment CF-1Q9Z4X7C5V is on hold at Lagos Port due to weather.','warning','admin/shipments.php',0),
(4,NULL,'Quote request','New quote request from Daniel Okafor.','info','admin/quotes.php',0);

INSERT INTO `quotes` (`id`,`name`,`email`,`phone`,`origin`,`destination`,`service_type`,`package_type`,`weight`,`message`,`estimated_price`,`status`) VALUES
(1,'Daniel Okafor','daniel@savannah.ng','+234 1 555 0166','Lagos, Nigeria','London, UK','air','parcel','120 kg','Need a quote for a recurring monthly air shipment.','820.00','new'),
(2,'Priya Patel','priya@indusltd.in','+91 22 555 0191','Mumbai, India','Dubai, UAE','sea','container','15 tons','Import/export of textiles.','2450.00','reviewed');

INSERT INTO `contact_messages` (`id`,`name`,`email`,`phone`,`subject`,`message`,`status`) VALUES
(1,'Marcus Chen','marcus@atlastech.io','+1 206 555 0177','Bulk shipping inquiry','Do you offer volume discounts for 50+ shipments per month?','new'),
(2,'Sofia Bianchi','sofia@bellaitalia.it','+39 02 555 0192','Customs support','Can you help with EU customs documentation?','read');

INSERT INTO `settings` (`setting_key`,`setting_value`) VALUES
('site_name','CargoFlow'),
('site_tagline','Logistics, reimagined.'),
('site_email','hello@cargoflow.test'),
('site_phone','+1 (800) 555-0199'),
('site_address','100 Logistics Way, San Francisco, CA 94105'),
('currency','USD'),
('currency_symbol','$'),
('tax_rate','8.5'),
('default_theme','light'),
('support_email','support@cargoflow.test'),
('company_registration','CF-REG-2026-001');
