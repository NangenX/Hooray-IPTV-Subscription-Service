-- ============================================
-- Hooray IPTV 订阅服务系统数据库初始化脚本
-- 创建日期: 2026-01-03
-- 数据库类型: MySQL 5.7+ / MariaDB 10.3+
-- ============================================

-- 设置字符集和排序规则
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. 管理员表 (admins)
-- 用途: 存储后台管理员账号信息
-- ============================================
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `email` varchar(255) NOT NULL COMMENT '邮箱',
  `password` varchar(255) NOT NULL COMMENT '密码(加密)',
  `role` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin' COMMENT '角色: 超级管理员/管理员/审核员',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态: 激活/停用',
  `permissions` json DEFAULT NULL COMMENT '权限配置(JSON格式)',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '创建者ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_username_unique` (`username`),
  UNIQUE KEY `admins_email_unique` (`email`),
  KEY `admins_created_by_foreign` (`created_by`),
  CONSTRAINT `admins_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- ============================================
-- 2. 用户表 (users)
-- 用途: 存储前端用户/订阅者信息
-- ============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `email` varchar(255) NOT NULL COMMENT '邮箱',
  `password` varchar(255) NOT NULL COMMENT '密码(加密)',
  `phone` varchar(255) DEFAULT NULL COMMENT '手机号',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active' COMMENT '状态: 激活/停用/封禁',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT '邮箱验证时间',
  `current_package_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '当前套餐ID',
  `language_preference` varchar(10) NOT NULL DEFAULT 'en' COMMENT '语言偏好',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- ============================================
-- 3. 频道表 (channels)
-- 用途: 存储IPTV频道信息
-- ============================================
DROP TABLE IF EXISTS `channels`;
CREATE TABLE `channels` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '频道名称',
  `description` text DEFAULT NULL COMMENT '频道描述',
  `stream_url` text NOT NULL COMMENT '流媒体URL',
  `logo_url` varchar(255) DEFAULT NULL COMMENT 'Logo图片URL',
  `category` varchar(255) DEFAULT NULL COMMENT '分类(如: 体育、新闻、电影)',
  `language` varchar(255) DEFAULT NULL COMMENT '语言',
  `country` varchar(2) DEFAULT NULL COMMENT '国家代码(ISO 3166-1 alpha-2)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否激活',
  `quality` varchar(10) DEFAULT NULL COMMENT '画质(SD/HD/FHD/4K)',
  `tvg_id` varchar(255) DEFAULT NULL COMMENT 'TVG ID(电子节目指南)',
  `tvg_name` varchar(255) DEFAULT NULL COMMENT 'TVG名称',
  `tvg_logo` varchar(255) DEFAULT NULL COMMENT 'TVG Logo',
  `group_title` varchar(255) DEFAULT NULL COMMENT '分组标题',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序顺序',
  `metadata` json DEFAULT NULL COMMENT '元数据(JSON格式)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name_url` (`name`, `stream_url`(255)) COMMENT '频道名称+URL唯一约束',
  KEY `channels_tvg_id_index` (`tvg_id`),
  KEY `channels_group_title_index` (`group_title`),
  KEY `channels_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='频道表';

-- ============================================
-- 4. 套餐表 (packages)
-- 用途: 存储订阅套餐信息
-- ============================================
DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '套餐名称',
  `description` text DEFAULT NULL COMMENT '套餐描述',
  `duration_days` int(11) NOT NULL COMMENT '有效期(天数)',
  `price` decimal(10,2) DEFAULT NULL COMMENT '价格',
  `max_devices` int(11) NOT NULL DEFAULT 1 COMMENT '最大设备数',
  `max_concurrent_streams` int(11) NOT NULL DEFAULT 1 COMMENT '最大并发流数',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否激活',
  `features` json DEFAULT NULL COMMENT '特性列表(JSON格式)',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序顺序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  KEY `packages_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐表';

-- ============================================
-- 5. 套餐频道关联表 (package_channels)
-- 用途: 存储套餐与频道的多对多关系
-- ============================================
DROP TABLE IF EXISTS `package_channels`;
CREATE TABLE `package_channels` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` bigint(20) UNSIGNED NOT NULL COMMENT '套餐ID',
  `channel_id` bigint(20) UNSIGNED NOT NULL COMMENT '频道ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `package_channels_package_id_channel_id_unique` (`package_id`, `channel_id`),
  KEY `package_channels_channel_id_foreign` (`channel_id`),
  CONSTRAINT `package_channels_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_channels_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐频道关联表';

-- ============================================
-- 6. 邀请码表 (invitation_codes)
-- 用途: 存储邀请码信息
-- ============================================
DROP TABLE IF EXISTS `invitation_codes`;
CREATE TABLE `invitation_codes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL COMMENT '邀请码',
  `package_id` bigint(20) UNSIGNED NOT NULL COMMENT '关联套餐ID',
  `max_uses` int(11) DEFAULT NULL COMMENT '最大使用次数(NULL为无限制)',
  `current_uses` int(11) NOT NULL DEFAULT 0 COMMENT '当前使用次数',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '过期时间',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否激活',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建者ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitation_codes_code_unique` (`code`),
  KEY `invitation_codes_code_index` (`code`),
  KEY `invitation_codes_is_active_index` (`is_active`),
  KEY `invitation_codes_package_id_foreign` (`package_id`),
  KEY `invitation_codes_created_by_foreign` (`created_by`),
  CONSTRAINT `invitation_codes_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invitation_codes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='邀请码表';

-- ============================================
-- 7. 订单表 (orders)
-- 用途: 存储用户订阅订单信息
-- ============================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) NOT NULL COMMENT '订单号',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
  `package_id` bigint(20) UNSIGNED NOT NULL COMMENT '套餐ID',
  `invitation_code_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '邀请码ID',
  `status` enum('active','expired','cancelled','suspended') NOT NULL DEFAULT 'active' COMMENT '状态: 激活/过期/取消/暂停',
  `starts_at` timestamp NOT NULL COMMENT '开始时间',
  `expires_at` timestamp NOT NULL COMMENT '到期时间',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `payment_status` enum('free','paid','pending') NOT NULL DEFAULT 'free' COMMENT '支付状态: 免费/已付款/待付款',
  `metadata` json DEFAULT NULL COMMENT '元数据(JSON格式)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_order_number_index` (`order_number`),
  KEY `orders_user_id_status_index` (`user_id`, `status`),
  KEY `orders_package_id_foreign` (`package_id`),
  KEY `orders_invitation_code_id_foreign` (`invitation_code_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_invitation_code_id_foreign` FOREIGN KEY (`invitation_code_id`) REFERENCES `invitation_codes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单表';

-- ============================================
-- 8. 导入日志表 (import_logs)
-- 用途: 存储M3U文件导入日志
-- ============================================
DROP TABLE IF EXISTS `import_logs`;
CREATE TABLE `import_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL COMMENT '文件名',
  `file_size` int(11) NOT NULL COMMENT '文件大小(字节)',
  `total_processed` int(11) NOT NULL DEFAULT 0 COMMENT '处理总数',
  `imported` int(11) NOT NULL DEFAULT 0 COMMENT '导入成功数',
  `skipped` int(11) NOT NULL DEFAULT 0 COMMENT '跳过数',
  `errors` int(11) NOT NULL DEFAULT 0 COMMENT '错误数',
  `log_file_path` text DEFAULT NULL COMMENT '详细日志文件路径',
  `error_details` json DEFAULT NULL COMMENT '错误详情(JSON格式)',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建者ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_logs_created_at_index` (`created_at`),
  KEY `import_logs_created_by_foreign` (`created_by`),
  CONSTRAINT `import_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='导入日志表';

-- ============================================
-- 初始数据插入
-- ============================================

-- 插入默认超级管理员
-- 用户名: admin
-- 密码: admin123 (加密后的哈希值)
-- 注意: 以下密码哈希是使用 Laravel Hash::make('admin123') 生成的示例
-- 实际使用时请在应用中通过 php artisan db:seed --class=AdminSeeder 创建
INSERT INTO `admins` (`id`, `username`, `email`, `password`, `role`, `status`, `permissions`, `last_login_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@iptv.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NULL, NULL, NULL, NOW(), NOW());

-- ============================================
-- 示例数据 (可选)
-- ============================================

-- 示例套餐
INSERT INTO `packages` (`name`, `description`, `duration_days`, `price`, `max_devices`, `max_concurrent_streams`, `is_active`, `features`, `sort_order`, `created_at`, `updated_at`) VALUES
('基础套餐', '包含100+频道，适合个人用户', 30, 9.99, 1, 1, 1, '["高清画质", "7x24客服"]', 1, NOW(), NOW()),
('标准套餐', '包含300+频道，支持多设备', 30, 19.99, 2, 2, 1, '["高清画质", "7x24客服", "多设备支持"]', 2, NOW(), NOW()),
('高级套餐', '包含500+频道，无限设备', 30, 29.99, 5, 3, 1, '["超清画质", "7x24客服", "多设备支持", "独享线路"]', 3, NOW(), NOW());

-- ============================================
-- 恢复外键检查
-- ============================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 使用说明
-- ============================================
-- 1. 创建数据库: CREATE DATABASE iptv_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- 2. 选择数据库: USE iptv_db;
-- 3. 执行此脚本: source database_init.sql;
-- 
-- 默认管理员账号:
--   用户名: admin
--   密码: admin123
--   邮箱: admin@iptv.local
--
-- 警告: 首次登录后请立即修改默认密码!
-- ============================================
