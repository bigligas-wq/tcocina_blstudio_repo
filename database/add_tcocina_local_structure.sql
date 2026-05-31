-- =============================================================================
-- Script para agregar tablas y columnas faltantes a tcocina_local
-- (Sistema de figuritas/loyalty, cupones, Google Auth)
-- Ejecutar en phpMyAdmin sobre la base tcocina_local
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. Tabla loyalty_settings (álbum de figuritas - configuración)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loyalty_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `target_stickers` int(10) UNSIGNED NOT NULL DEFAULT 10,
  `reward_type` varchar(255) NOT NULL DEFAULT 'text',
  `reward_value` varchar(255) NOT NULL,
  `reward_description` text DEFAULT NULL,
  `album_help_message` text DEFAULT NULL,
  `reward_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_settings_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `loyalty_settings` (`target_stickers`, `reward_type`, `reward_value`, `reward_description`, `album_help_message`, `reward_image`, `is_active`, `created_at`, `updated_at`)
VALUES (10, 'text', 'Combo de regalo', NULL, NULL, NULL, 1, NOW(), NOW());

-- -----------------------------------------------------------------------------
-- 2. Tabla user_loyalty_wallets (billetera de figuritas por usuario)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_loyalty_wallets` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `current_stickers` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_earned` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_redeemed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_loyalty_wallets_user_id_unique` (`user_id`),
  CONSTRAINT `user_loyalty_wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Tabla user_loyalty_movements (movimientos de figuritas)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_loyalty_movements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `delta` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_loyalty_movements_user_id_created_at_index` (`user_id`, `created_at`),
  KEY `user_loyalty_movements_reason_created_at_index` (`reason`, `created_at`),
  UNIQUE KEY `user_loyalty_movements_user_id_order_id_reason_unique` (`user_id`, `order_id`, `reason`),
  CONSTRAINT `user_loyalty_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_loyalty_movements_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_loyalty_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Tabla loyalty_redemptions (canjes de premios)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loyalty_redemptions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `stickers_spent` int(10) UNSIGNED NOT NULL,
  `reward_snapshot` json NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_redemptions_user_id_status_index` (`user_id`, `status`),
  KEY `loyalty_redemptions_status_created_at_index` (`status`, `created_at`),
  CONSTRAINT `loyalty_redemptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_redemptions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. Tabla coupon_order (relación pedidos-cupones)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `coupon_order` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `discount_applied` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `coupon_order_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_order_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. orders: agregar coupon_id
-- -----------------------------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'coupon_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `orders` ADD COLUMN `coupon_id` bigint(20) UNSIGNED NULL DEFAULT NULL AFTER `discount_amount`, ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 7. users: agregar google_id, avatar; password nullable
-- -----------------------------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'google_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `google_id` varchar(255) NULL UNIQUE AFTER `email`, ADD COLUMN `avatar` varchar(255) NULL AFTER `phone`, MODIFY COLUMN `password` varchar(255) NULL',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Si google_id ya existe pero password no es nullable:
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'google_id');
SET @sql = IF(@col_exists = 1,
  'ALTER TABLE `users` MODIFY COLUMN `password` varchar(255) NULL',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 8. coupons: agregar discount_percentage, code_length, used_count, allow_cash_discount
-- -----------------------------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'coupons' AND COLUMN_NAME = 'discount_percentage');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `coupons` ADD COLUMN `discount_percentage` decimal(5,2) NULL AFTER `value`, ADD COLUMN `code_length` int(11) NOT NULL DEFAULT 8 AFTER `discount_percentage`, ADD COLUMN `used_count` int(11) NOT NULL DEFAULT 0 AFTER `usage_count`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'coupons' AND COLUMN_NAME = 'allow_cash_discount');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `coupons` ADD COLUMN `allow_cash_discount` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_active`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar datos en coupons si las columnas existen
UPDATE `coupons` SET `discount_percentage` = `value` WHERE `type` = 'percentage' AND `discount_percentage` IS NULL;
UPDATE `coupons` SET `code_length` = LENGTH(`code`) WHERE `code_length` = 0 OR `code_length` IS NULL;
UPDATE `coupons` SET `used_count` = `usage_count` WHERE `used_count` = 0 AND `usage_count` > 0;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Fin del script
-- =============================================================================
