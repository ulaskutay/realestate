-- Video Timeline Modülü - Tablolar
-- Modül kurulumunda veya manuel çalıştırılabilir

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `video_timelines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `width` int(11) NOT NULL DEFAULT 1920,
  `height` int(11) NOT NULL DEFAULT 1080,
  `fps` int(11) NOT NULL DEFAULT 25,
  `duration_sec` decimal(10,2) NOT NULL DEFAULT 10.00,
  `background_color` varchar(50) DEFAULT '#000000',
  `settings` JSON DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `video_timeline_tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeline_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'Track',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `is_muted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `timeline_id` (`timeline_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `video_timeline_clips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `track_id` int(11) NOT NULL,
  `type` enum('video','image','text','shape','audio') NOT NULL DEFAULT 'text',
  `start_time` decimal(10,3) NOT NULL DEFAULT 0.000,
  `duration` decimal(10,3) NOT NULL DEFAULT 5.000,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `source` varchar(500) DEFAULT NULL,
  `content` JSON DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `track_id` (`track_id`),
  KEY `type` (`type`),
  KEY `start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
