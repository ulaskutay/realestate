-- SEO Sayfa Meta tablosu (sayfa bazlı meta title/description override)
CREATE TABLE IF NOT EXISTS `seo_page_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_key` varchar(100) NOT NULL COMMENT 'Sayfa anahtarı: home, blog, contact, ilanlar, danismanlar, vb.',
  `path_pattern` varchar(500) DEFAULT '' COMMENT 'Boş = varsayılan sayfa; dolu = özel path',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `meta_robots` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_key_path` (`page_key`,`path_pattern`(191)),
  KEY `page_key` (`page_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEO Kırık Bağlantılar tablosu (404 tespit sonuçları)
CREATE TABLE IF NOT EXISTS `seo_broken_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(1000) NOT NULL,
  `source` varchar(50) DEFAULT NULL COMMENT 'sitemap, menu, manual',
  `http_code` int(11) DEFAULT NULL,
  `checked_at` datetime DEFAULT NULL,
  `link_text` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `http_code` (`http_code`),
  KEY `checked_at` (`checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
