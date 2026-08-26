-- =====================================================================
--  Migration: add sender / receiver details to shipments
--  Run this once on an EXISTING database (new installs already get the
--  columns from database/schema.sql). The app also auto-applies these
--  columns on first use, so running it manually is optional.
-- =====================================================================

ALTER TABLE `shipments`
  ADD COLUMN `sender_name` VARCHAR(160) DEFAULT NULL AFTER `customer_id`,
  ADD COLUMN `sender_details` TEXT AFTER `sender_name`,
  ADD COLUMN `receiver_name` VARCHAR(160) DEFAULT NULL AFTER `sender_details`,
  ADD COLUMN `receiver_details` TEXT AFTER `receiver_name`;
