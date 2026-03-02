-- Sözleşme şablonları tablosu (Elementor tarzı editör)
CREATE TABLE IF NOT EXISTS `contract_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Şablon adı',
  `slug` varchar(100) NOT NULL COMMENT 'URL/ayırt etmek için',
  `header_config` json DEFAULT NULL COMMENT 'Başlık: sol/orta/sağ alan tanımları',
  `table_config` json DEFAULT NULL COMMENT 'Orta tablo: satır/sütun, başlıklar, hücre tipleri, tema rengi',
  `footer_config` json DEFAULT NULL COMMENT 'Alt: açıklama + imza etiketleri',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- commission_contracts tablosuna şablon ve form verisi kolonları
ALTER TABLE `commission_contracts`
  ADD COLUMN `template_id` int(11) DEFAULT NULL AFTER `client_email`,
  ADD COLUMN `form_data` json DEFAULT NULL AFTER `template_id`,
  ADD KEY `template_id` (`template_id`);

ALTER TABLE `contract_templates` ADD COLUMN `name` varchar(255) NOT NULL DEFAULT '' AFTER `id`;