-- Komple CMS Veritabanı Şeması - Foreign Key'siz Versiyon
-- Tüm tabloları sırayla oluşturur (Constraint'ler olmadan)
-- MySQL 5.7+ ve MariaDB 10.2+ uyumludur

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================
-- 1. KULLANICILAR VE ROLLER
-- ==========================================

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `role` enum('super_admin','admin','editor','author','subscriber') DEFAULT 'subscriber',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roller tablosu
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` JSON DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rol yetkileri tablosu
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_module_permission` (`role_id`, `module`, `permission`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 2. MODÜL SİSTEMİ
-- ==========================================

-- Modüller tablosu
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `version` varchar(20) DEFAULT '1.0.0',
  `author` varchar(100) DEFAULT NULL,
  `author_url` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'extension',
  `path` varchar(255) DEFAULT NULL,
  `main_file` varchar(100) DEFAULT 'Controller.php',
  `settings` JSON DEFAULT NULL,
  `dependencies` JSON DEFAULT NULL,
  `requires_php` varchar(10) DEFAULT '7.4',
  `requires_cms` varchar(10) DEFAULT '1.0',
  `is_active` tinyint(1) DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `has_settings` tinyint(1) DEFAULT 0,
  `has_frontend` tinyint(1) DEFAULT 0,
  `has_admin` tinyint(1) DEFAULT 1,
  `has_widgets` tinyint(1) DEFAULT 0,
  `has_shortcodes` tinyint(1) DEFAULT 0,
  `menu_position` int(11) DEFAULT 100,
  `installed_at` datetime DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`),
  KEY `is_system` (`is_system`),
  KEY `menu_position` (`menu_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modül yetkileri tablosu
CREATE TABLE IF NOT EXISTS `module_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_permission` (`module_id`, `permission`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 3. SİSTEM AYARLARI
-- ==========================================

-- Sistem ayarları tablosu
CREATE TABLE IF NOT EXISTS `options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) NOT NULL,
  `option_value` longtext,
  `autoload` enum('yes','no') DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 4. İÇERİKLER (POSTS & PAGES)
-- ==========================================

-- Yazı kategorileri
CREATE TABLE IF NOT EXISTS `post_categories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Yazı etiketleri
CREATE TABLE IF NOT EXISTS `post_tags` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Yazılar tablosu
CREATE TABLE IF NOT EXISTS `posts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `slug` varchar(500) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `category_id` bigint(20) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published','scheduled','trash') DEFAULT 'draft',
  `type` enum('post','page') DEFAULT 'post',
  `visibility` enum('public','private','password') DEFAULT 'public',
  `password` varchar(255) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `version` int(11) DEFAULT 1 COMMENT 'Versiyon numarası',
  `allow_comments` tinyint(1) DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `published_at` (`published_at`),
  KEY `visibility` (`visibility`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Yazı-Etiket ilişkileri
CREATE TABLE IF NOT EXISTS `post_tag_relations` (
  `post_id` bigint(20) NOT NULL,
  `tag_id` bigint(20) NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Yazı versiyonları
CREATE TABLE IF NOT EXISTS `post_versions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `version_number` int(11) DEFAULT 1 COMMENT 'Versiyon numarası',
  `title` varchar(500) DEFAULT NULL,
  `slug` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `version_note` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `version_number` (`version_number`),
  KEY `created_by` (`created_by`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sayfa meta verileri
CREATE TABLE IF NOT EXISTS `page_meta` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `meta_key` (`meta_key`),
  UNIQUE KEY `page_meta_unique` (`page_id`, `meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 5. MEDYA KÜTÜPHANESI
-- ==========================================

CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) DEFAULT 0,
  `file_path` varchar(500) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `mime_type` (`mime_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 6. SLIDER SİSTEMİ
-- ==========================================

CREATE TABLE IF NOT EXISTS `sliders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `width` int(11) DEFAULT 1920,
  `height` int(11) DEFAULT 600,
  `autoplay` tinyint(1) DEFAULT 1,
  `autoplay_speed` int(11) DEFAULT 5000,
  `transition` varchar(50) DEFAULT 'fade',
  `navigation` tinyint(1) DEFAULT 1,
  `pagination` tinyint(1) DEFAULT 1,
  `loop` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `slider_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slider_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `image_mobile` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_url` varchar(500) DEFAULT NULL,
  `button_target` enum('_self','_blank') DEFAULT '_self',
  `text_position` varchar(50) DEFAULT 'center',
  `text_color` varchar(20) DEFAULT '#ffffff',
  `overlay_color` varchar(20) DEFAULT 'rgba(0,0,0,0.3)',
  `custom_css` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `slider_id` (`slider_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `slider_layers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slider_item_id` int(11) NOT NULL,
  `layer_type` enum('text','heading','button','image','shape') DEFAULT 'text',
  `content` text DEFAULT NULL,
  `position_x` int(11) DEFAULT 0,
  `position_y` int(11) DEFAULT 0,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `font_size` int(11) DEFAULT 16,
  `font_weight` varchar(20) DEFAULT 'normal',
  `text_color` varchar(20) DEFAULT '#000000',
  `background_color` varchar(20) DEFAULT 'transparent',
  `border_radius` int(11) DEFAULT 0,
  `padding` varchar(50) DEFAULT '10px',
  `animation` varchar(50) DEFAULT 'fade',
  `animation_delay` int(11) DEFAULT 0,
  `animation_duration` int(11) DEFAULT 1000,
  `link_url` varchar(500) DEFAULT NULL,
  `link_target` enum('_self','_blank') DEFAULT '_self',
  `custom_class` varchar(100) DEFAULT NULL,
  `custom_css` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `slider_item_id` (`slider_item_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `slider_backgrounds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slider_item_id` int(11) NOT NULL,
  `type` enum('image','video','gradient','color') DEFAULT 'image',
  `image_url` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `video_poster` varchar(500) DEFAULT NULL,
  `gradient_start` varchar(20) DEFAULT NULL,
  `gradient_end` varchar(20) DEFAULT NULL,
  `gradient_direction` enum('to right','to left','to bottom','to top','to bottom right','to bottom left','to top right','to top left') DEFAULT 'to right',
  `color` varchar(20) DEFAULT NULL,
  `parallax_enabled` tinyint(1) DEFAULT 0,
  `parallax_speed` decimal(3,2) DEFAULT 0.50,
  `ken_burns_enabled` tinyint(1) DEFAULT 0,
  `ken_burns_settings` text DEFAULT NULL,
  `overlay_enabled` tinyint(1) DEFAULT 0,
  `overlay_color` varchar(20) DEFAULT NULL,
  `overlay_opacity` decimal(3,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slider_item_id` (`slider_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 7. MENÜ SİSTEMİ
-- ==========================================

CREATE TABLE IF NOT EXISTS `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `location` (`location`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `target` enum('_self','_blank') DEFAULT '_self',
  `type` varchar(50) DEFAULT 'custom' COMMENT 'Menü tipi: custom, post, page, category',
  `type_id` int(11) DEFAULT NULL COMMENT 'Post/Page/Category ID',
  `icon` varchar(100) DEFAULT NULL,
  `css_class` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT 0 COMMENT 'Sıralama',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Eski sıralama (uyumluluk)',
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  KEY `parent_id` (`parent_id`),
  KEY `order` (`order`),
  KEY `sort_order` (`sort_order`),
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 8. FORM SİSTEMİ
-- ==========================================

CREATE TABLE IF NOT EXISTS `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `submit_button_text` varchar(100) DEFAULT 'Gönder',
  `submit_button_color` varchar(7) DEFAULT '#137fec',
  `form_style` varchar(50) DEFAULT 'default',
  `layout` varchar(50) DEFAULT 'vertical',
  `success_message` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `redirect_url` varchar(500) DEFAULT NULL,
  `email_notification` tinyint(1) DEFAULT 1,
  `notification_email` varchar(255) DEFAULT NULL,
  `email_subject` varchar(255) DEFAULT NULL,
  `settings` JSON DEFAULT NULL,
  `submission_count` int(11) DEFAULT 0 COMMENT 'Toplam form gönderim sayısı',
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `form_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `field_type` varchar(50) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_label` varchar(255) DEFAULT NULL,
  `placeholder` varchar(255) DEFAULT NULL,
  `default_value` text DEFAULT NULL,
  `options` JSON DEFAULT NULL,
  `validation_rules` JSON DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `help_text` text DEFAULT NULL,
  `css_class` varchar(255) DEFAULT NULL,
  `width` varchar(50) DEFAULT 'full',
  `order` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`),
  KEY `order` (`order`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `data` JSON DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('new','read','spam','archived') DEFAULT 'new',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 9. ANALİTİK SİSTEMİ
-- ==========================================

CREATE TABLE IF NOT EXISTS `analytics` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(500) NOT NULL,
  `page_title` varchar(255) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `visit_time` time DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `page_url` (`page_url`(191)),
  KEY `visit_date` (`visit_date`),
  KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Privacy-friendly analytics tablo (page_views)
CREATE TABLE IF NOT EXISTS `page_views` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_hash` VARCHAR(64) NOT NULL COMMENT 'Anonim session hash (IP + User-Agent hash)',
  `page_url` VARCHAR(500) NOT NULL COMMENT 'Ziyaret edilen sayfa URL',
  `page_title` VARCHAR(255) DEFAULT NULL COMMENT 'Sayfa başlığı',
  `referrer` VARCHAR(500) DEFAULT NULL COMMENT 'Yönlendiren URL',
  `user_agent` TEXT DEFAULT NULL COMMENT 'Tarayıcı bilgisi',
  `device_type` ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop' COMMENT 'Cihaz tipi',
  `is_bot` TINYINT(1) DEFAULT 0 COMMENT 'Bot olup olmadığı',
  `visit_duration` INT(11) DEFAULT 0 COMMENT 'Sayfada kalma süresi (saniye)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Ziyaret zamanı',
  PRIMARY KEY (`id`),
  KEY `idx_session_hash` (`session_hash`),
  KEY `idx_page_url` (`page_url`(191)),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_device_type` (`device_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 10. SÖZLEŞMELER
-- ==========================================

CREATE TABLE IF NOT EXISTS `agreements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `type` enum('privacy','terms','cookie','kvkk','other') DEFAULT 'other',
  `version` varchar(20) DEFAULT '1.0',
  `status` enum('draft','published','archived') DEFAULT 'published',
  `author_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `requires_acceptance` tinyint(1) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `is_active` (`is_active`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `agreement_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agreement_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `version` varchar(20) NOT NULL,
  `version_number` int(11) DEFAULT 1,
  `content` longtext NOT NULL,
  `changes` text DEFAULT NULL,
  `change_note` text DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `agreement_id` (`agreement_id`),
  KEY `author_id` (`author_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 11. TEMA SİSTEMİ
-- ==========================================

CREATE TABLE IF NOT EXISTS `themes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(100) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `version` VARCHAR(20) DEFAULT '1.0.0',
  `author` VARCHAR(255) DEFAULT NULL,
  `author_url` VARCHAR(500) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `screenshot` VARCHAR(500) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 0,
  `settings_schema` JSON DEFAULT NULL,
  `installed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `theme_options` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `theme_id` INT(11) NOT NULL,
  `option_group` VARCHAR(50) DEFAULT 'general',
  `option_key` VARCHAR(100) NOT NULL,
  `option_value` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `theme_option` (`theme_id`, `option_key`),
  KEY `theme_id` (`theme_id`),
  KEY `option_group` (`option_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `page_sections` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `theme_id` INT(11) DEFAULT NULL,
  `page_type` VARCHAR(50) NOT NULL,
  `section_id` VARCHAR(100) NOT NULL,
  `section_component` VARCHAR(100) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `subtitle` VARCHAR(500) DEFAULT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `settings` JSON DEFAULT NULL,
  `items` JSON DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `theme_id` (`theme_id`),
  KEY `page_type` (`page_type`),
  KEY `section_id` (`section_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `theme_custom_code` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `theme_id` INT(11) NOT NULL,
  `code_type` ENUM('css', 'js', 'head', 'footer') NOT NULL DEFAULT 'css',
  `code_content` LONGTEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `theme_code_type` (`theme_id`, `code_type`),
  KEY `theme_id` (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- VARSAYILAN VERİLER
-- ==========================================

-- Varsayılan roller
INSERT IGNORE INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`) VALUES
(1, 'Süper Admin', 'super_admin', 'Tam yetki sahibi sistem yöneticisi', 1),
(2, 'Admin', 'admin', 'Yönetim paneline tam erişim', 1),
(3, 'Editör', 'editor', 'İçerik yönetimi ve düzenleme', 1),
(4, 'Yazar', 'author', 'İçerik oluşturma ve kendi içeriklerini düzenleme', 1),
(5, 'Abone', 'subscriber', 'Temel kullanıcı', 1);

-- Admin rolüne tüm yetkileri ver (ID: 2)
INSERT IGNORE INTO `role_permissions` (`role_id`, `module`, `permission`) VALUES
-- Admin rolü - Tüm yetkiler
(2, 'system', '*'),
-- Yazılar
(2, 'posts', 'posts.view'),
(2, 'posts', 'posts.create'),
(2, 'posts', 'posts.edit'),
(2, 'posts', 'posts.delete'),
(2, 'posts', 'posts.publish'),
-- Sayfalar
(2, 'pages', 'pages.view'),
(2, 'pages', 'pages.create'),
(2, 'pages', 'pages.edit'),
(2, 'pages', 'pages.delete'),
(2, 'pages', 'pages.publish'),
-- Medya
(2, 'media', 'media.view'),
(2, 'media', 'media.upload'),
(2, 'media', 'media.edit'),
(2, 'media', 'media.delete'),
-- Formlar
(2, 'forms', 'forms.view'),
(2, 'forms', 'forms.create'),
(2, 'forms', 'forms.edit'),
(2, 'forms', 'forms.delete'),
(2, 'forms', 'forms.submissions'),
-- Sözleşmeler
(2, 'agreements', 'agreements.view'),
(2, 'agreements', 'agreements.create'),
(2, 'agreements', 'agreements.edit'),
(2, 'agreements', 'agreements.delete'),
-- Sliderlar
(2, 'sliders', 'sliders.view'),
(2, 'sliders', 'sliders.create'),
(2, 'sliders', 'sliders.edit'),
(2, 'sliders', 'sliders.delete'),
-- Menüler
(2, 'menus', 'menus.view'),
(2, 'menus', 'menus.create'),
(2, 'menus', 'menus.edit'),
(2, 'menus', 'menus.delete'),
-- Kullanıcılar
(2, 'users', 'users.view'),
(2, 'users', 'users.create'),
(2, 'users', 'users.edit'),
(2, 'users', 'users.delete'),
-- Roller
(2, 'roles', 'roles.view'),
(2, 'roles', 'roles.create'),
(2, 'roles', 'roles.edit'),
(2, 'roles', 'roles.delete'),
-- Temalar
(2, 'themes', 'themes.view'),
(2, 'themes', 'themes.edit'),
(2, 'themes', 'themes.edit_code'),
-- Ayarlar
(2, 'settings', 'settings.view'),
(2, 'settings', 'settings.edit'),
-- Modüller
(2, 'modules', 'modules.view'),
(2, 'modules', 'modules.manage');

-- Editör rolüne yetkiler (ID: 3)
INSERT IGNORE INTO `role_permissions` (`role_id`, `module`, `permission`) VALUES
-- Yazılar
(3, 'posts', 'posts.view'),
(3, 'posts', 'posts.create'),
(3, 'posts', 'posts.edit'),
(3, 'posts', 'posts.delete'),
(3, 'posts', 'posts.publish'),
-- Sayfalar
(3, 'pages', 'pages.view'),
(3, 'pages', 'pages.create'),
(3, 'pages', 'pages.edit'),
(3, 'pages', 'pages.delete'),
(3, 'pages', 'pages.publish'),
-- Medya
(3, 'media', 'media.view'),
(3, 'media', 'media.upload'),
(3, 'media', 'media.edit'),
(3, 'media', 'media.delete'),
-- Formlar
(3, 'forms', 'forms.view'),
(3, 'forms', 'forms.create'),
(3, 'forms', 'forms.edit'),
(3, 'forms', 'forms.delete'),
(3, 'forms', 'forms.submissions'),
-- Sözleşmeler
(3, 'agreements', 'agreements.view'),
(3, 'agreements', 'agreements.create'),
(3, 'agreements', 'agreements.edit'),
(3, 'agreements', 'agreements.delete'),
-- Sliderlar
(3, 'sliders', 'sliders.view'),
(3, 'sliders', 'sliders.create'),
(3, 'sliders', 'sliders.edit'),
(3, 'sliders', 'sliders.delete'),
-- Menüler
(3, 'menus', 'menus.view'),
(3, 'menus', 'menus.create'),
(3, 'menus', 'menus.edit'),
(3, 'menus', 'menus.delete');

-- Yazar rolüne yetkiler (ID: 4)
INSERT IGNORE INTO `role_permissions` (`role_id`, `module`, `permission`) VALUES
-- Yazılar
(4, 'posts', 'posts.view'),
(4, 'posts', 'posts.create'),
(4, 'posts', 'posts.edit'),
-- Sayfalar
(4, 'pages', 'pages.view'),
(4, 'pages', 'pages.create'),
(4, 'pages', 'pages.edit'),
-- Medya
(4, 'media', 'media.view'),
(4, 'media', 'media.upload');

-- Abone rolüne yetkiler (ID: 5)
INSERT IGNORE INTO `role_permissions` (`role_id`, `module`, `permission`) VALUES
(5, 'posts', 'posts.view'),
(5, 'media', 'media.view');

-- Varsayılan modüller
INSERT IGNORE INTO `modules` (`id`, `name`, `slug`, `label`, `description`, `icon`, `is_active`, `is_system`, `has_admin`, `menu_position`, `installed_at`, `created_at`) VALUES
(1, 'posts', 'posts', 'Yazılar', 'Blog yazıları ve içerik yönetimi', 'article', 1, 1, 1, 10, NOW(), NOW()),
(2, 'sliders', 'sliders', 'Sliderlar', 'Slider ve banner yönetimi', 'slideshow', 1, 1, 1, 20, NOW(), NOW()),
(3, 'menus', 'menus', 'Menüler', 'Navigasyon menü yönetimi', 'menu', 1, 1, 1, 30, NOW(), NOW()),
(4, 'forms', 'forms', 'Formlar', 'Form oluşturucu ve yönetimi', 'dynamic_form', 1, 1, 1, 40, NOW(), NOW()),
(5, 'media', 'media', 'Medya', 'Dosya ve görsel kütüphanesi', 'perm_media', 1, 1, 1, 50, NOW(), NOW()),
(6, 'users', 'users', 'Kullanıcılar', 'Kullanıcı ve rol yönetimi', 'people', 1, 1, 1, 60, NOW(), NOW()),
(7, 'design', 'design', 'Tasarım', 'Frontend tasarım düzenleme', 'palette', 1, 1, 1, 70, NOW(), NOW()),
(8, 'settings', 'settings', 'Ayarlar', 'Sistem ayarları', 'settings', 1, 1, 1, 100, NOW(), NOW()),
(9, 'seo', 'seo', 'SEO', 'Arama motoru optimizasyonu', 'search', 1, 0, 1, 80, NOW(), NOW()),
(10, 'cache', 'cache', 'Önbellek', 'Sistem önbellek yönetimi', 'cached', 1, 0, 1, 90, NOW(), NOW()),
(11, 'pages', 'pages', 'Sayfalar', 'Sayfa yönetimi', 'description', 1, 1, 1, 15, NOW(), NOW()),
(12, 'agreements', 'agreements', 'Sözleşmeler', 'Sözleşme ve politika yönetimi', 'gavel', 1, 1, 1, 45, NOW(), NOW()),
(13, 'roles', 'roles', 'Roller', 'Kullanıcı rol yönetimi', 'admin_panel_settings', 1, 1, 1, 65, NOW(), NOW()),
(14, 'themes', 'themes', 'Temalar', 'Tema yönetimi', 'brush', 1, 1, 1, 75, NOW(), NOW());

-- Modül Yetkileri (module_permissions)
INSERT IGNORE INTO `module_permissions` (`module_id`, `permission`, `label`, `description`, `sort_order`) VALUES
-- Yazılar (module_id: 1)
(1, 'posts.view', 'Yazıları Görüntüle', 'Yazı listesini görüntüleme', 1),
(1, 'posts.create', 'Yazı Oluştur', 'Yeni yazı oluşturma', 2),
(1, 'posts.edit', 'Yazı Düzenle', 'Mevcut yazıları düzenleme', 3),
(1, 'posts.delete', 'Yazı Sil', 'Yazıları silme', 4),
(1, 'posts.publish', 'Yazı Yayınla', 'Yazıları yayınlama', 5),
-- Sliderlar (module_id: 2)
(2, 'sliders.view', 'Sliderları Görüntüle', 'Slider listesini görüntüleme', 1),
(2, 'sliders.create', 'Slider Oluştur', 'Yeni slider oluşturma', 2),
(2, 'sliders.edit', 'Slider Düzenle', 'Mevcut sliderları düzenleme', 3),
(2, 'sliders.delete', 'Slider Sil', 'Sliderları silme', 4),
-- Menüler (module_id: 3)
(3, 'menus.view', 'Menüleri Görüntüle', 'Menü listesini görüntüleme', 1),
(3, 'menus.create', 'Menü Oluştur', 'Yeni menü oluşturma', 2),
(3, 'menus.edit', 'Menü Düzenle', 'Mevcut menüleri düzenleme', 3),
(3, 'menus.delete', 'Menü Sil', 'Menüleri silme', 4),
-- Formlar (module_id: 4)
(4, 'forms.view', 'Formları Görüntüle', 'Form listesini görüntüleme', 1),
(4, 'forms.create', 'Form Oluştur', 'Yeni form oluşturma', 2),
(4, 'forms.edit', 'Form Düzenle', 'Mevcut formları düzenleme', 3),
(4, 'forms.delete', 'Form Sil', 'Formları silme', 4),
(4, 'forms.submissions', 'Form Gönderimlerini Görüntüle', 'Form gönderimlerini görüntüleme', 5),
-- Medya (module_id: 5)
(5, 'media.view', 'Medya Görüntüle', 'Medya kütüphanesini görüntüleme', 1),
(5, 'media.upload', 'Medya Yükle', 'Yeni dosya yükleme', 2),
(5, 'media.edit', 'Medya Düzenle', 'Mevcut dosyaları düzenleme', 3),
(5, 'media.delete', 'Medya Sil', 'Dosyaları silme', 4),
-- Kullanıcılar (module_id: 6)
(6, 'users.view', 'Kullanıcıları Görüntüle', 'Kullanıcı listesini görüntüleme', 1),
(6, 'users.create', 'Kullanıcı Oluştur', 'Yeni kullanıcı oluşturma', 2),
(6, 'users.edit', 'Kullanıcı Düzenle', 'Mevcut kullanıcıları düzenleme', 3),
(6, 'users.delete', 'Kullanıcı Sil', 'Kullanıcıları silme', 4),
-- Tasarım (module_id: 7)
(7, 'design.view', 'Tasarımı Görüntüle', 'Tasarım sayfasını görüntüleme', 1),
(7, 'design.edit', 'Tasarımı Düzenle', 'Tasarım değişiklikleri yapma', 2),
-- Ayarlar (module_id: 8)
(8, 'settings.view', 'Ayarları Görüntüle', 'Ayarlar sayfasını görüntüleme', 1),
(8, 'settings.edit', 'Ayarları Düzenle', 'Sistem ayarlarını değiştirme', 2),
-- SEO (module_id: 9)
(9, 'seo.view', 'SEO Görüntüle', 'SEO ayarlarını görüntüleme', 1),
(9, 'seo.edit', 'SEO Düzenle', 'SEO ayarlarını değiştirme', 2),
-- Önbellek (module_id: 10)
(10, 'cache.view', 'Önbellek Görüntüle', 'Önbellek ayarlarını görüntüleme', 1),
(10, 'cache.manage', 'Önbellek Yönet', 'Önbelleği temizleme ve yönetme', 2),
-- Sayfalar (module_id: 11)
(11, 'pages.view', 'Sayfaları Görüntüle', 'Sayfa listesini görüntüleme', 1),
(11, 'pages.create', 'Sayfa Oluştur', 'Yeni sayfa oluşturma', 2),
(11, 'pages.edit', 'Sayfa Düzenle', 'Mevcut sayfaları düzenleme', 3),
(11, 'pages.delete', 'Sayfa Sil', 'Sayfaları silme', 4),
(11, 'pages.publish', 'Sayfa Yayınla', 'Sayfaları yayınlama', 5),
-- Sözleşmeler (module_id: 12)
(12, 'agreements.view', 'Sözleşmeleri Görüntüle', 'Sözleşme listesini görüntüleme', 1),
(12, 'agreements.create', 'Sözleşme Oluştur', 'Yeni sözleşme oluşturma', 2),
(12, 'agreements.edit', 'Sözleşme Düzenle', 'Mevcut sözleşmeleri düzenleme', 3),
(12, 'agreements.delete', 'Sözleşme Sil', 'Sözleşmeleri silme', 4),
-- Roller (module_id: 13)
(13, 'roles.view', 'Rolleri Görüntüle', 'Rol listesini görüntüleme', 1),
(13, 'roles.create', 'Rol Oluştur', 'Yeni rol oluşturma', 2),
(13, 'roles.edit', 'Rol Düzenle', 'Mevcut rolleri düzenleme', 3),
(13, 'roles.delete', 'Rol Sil', 'Rolleri silme', 4),
-- Temalar (module_id: 14)
(14, 'themes.view', 'Temaları Görüntüle', 'Tema listesini görüntüleme', 1),
(14, 'themes.edit', 'Tema Düzenle', 'Tema ayarlarını düzenleme', 2),
(14, 'themes.edit_code', 'Tema Kodunu Düzenle', 'Tema dosyalarını düzenleme', 3);

-- Varsayılan kategori
INSERT IGNORE INTO `post_categories` (`id`, `name`, `slug`, `description`, `status`) VALUES
(1, 'Genel', 'genel', 'Genel yazılar kategorisi', 'active');

-- Varsayılan tema (Starter)
INSERT IGNORE INTO `themes` (`id`, `slug`, `name`, `version`, `author`, `description`, `is_active`, `installed_at`) VALUES
(1, 'starter', 'Starter Theme', '1.0.0', 'Codetic', 'Modern ve minimal başlangıç teması', 1, NOW());

-- Varsayılan tema ayarları (colors)
INSERT IGNORE INTO `theme_options` (`theme_id`, `option_group`, `option_key`, `option_value`) VALUES
(1, 'colors', 'primary', '#137fec'),
(1, 'colors', 'secondary', '#6366f1'),
(1, 'colors', 'accent', '#10b981'),
(1, 'colors', 'background', '#ffffff'),
(1, 'colors', 'surface', '#f8fafc'),
(1, 'colors', 'text', '#1f2937'),
(1, 'colors', 'text_muted', '#6b7280'),
(1, 'fonts', 'heading', 'Poppins'),
(1, 'fonts', 'body', 'Inter'),
(1, 'custom', 'header_style', 'fixed'),
(1, 'custom', 'header_transparent', 'false'),
(1, 'custom', 'show_breadcrumb', 'true'),
(1, 'custom', 'footer_columns', '4'),
(1, 'custom', 'show_back_to_top', 'true'),
(1, 'custom', 'footer_bg_color', '#111827'),
(1, 'custom', 'footer_text_color', '#ffffff'),
(1, 'custom', 'footer_show', 'true'),
(1, 'custom', 'footer_copyright_text', 'Tüm hakları saklıdır.'),
(1, 'custom', 'footer_copyright_year', 'auto'),
(1, 'custom', 'footer_show_social', 'true'),
(1, 'custom', 'footer_show_menu', 'true'),
(1, 'custom', 'footer_show_contact', 'true');

SET FOREIGN_KEY_CHECKS = 1;
