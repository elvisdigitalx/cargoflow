-- =====================================================================
--  Migration: add package_image to shipments
--  Run this once on an EXISTING database (new installs already get the
--  column from database/schema.sql). The app also auto-applies this
--  column on first use, so running it manually is optional.
-- =====================================================================

ALTER TABLE `shipments`
  ADD COLUMN `package_image` VARCHAR(255) DEFAULT NULL AFTER `package_type`;
