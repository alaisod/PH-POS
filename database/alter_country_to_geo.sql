-- SQL command for hosting (run manually)
ALTER TABLE `phppos_people`
  ADD COLUMN `latitude` decimal(10,8) NOT NULL DEFAULT 0.00000000 AFTER `zip`,
  ADD COLUMN `longitude` decimal(11,8) NOT NULL DEFAULT 0.00000000 AFTER `latitude`,
  DROP COLUMN `country`;
