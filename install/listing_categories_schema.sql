-- İlan kategorileri (satılık, kiralık, daire, müstakil vb. tek listede)
CREATE TABLE IF NOT EXISTS `listing_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `kind` varchar(20) NOT NULL DEFAULT 'type',
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `kind` (`kind`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- İlan–kategori ilişkisi (çoklu kategori)
CREATE TABLE IF NOT EXISTS `realestate_listing_categories` (
  `listing_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`listing_id`, `category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `rlc_listing` FOREIGN KEY (`listing_id`) REFERENCES `realestate_listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rlc_category` FOREIGN KEY (`category_id`) REFERENCES `listing_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
