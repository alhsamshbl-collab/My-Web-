-- نفّذ هذا الملف مرة واحدة فقط على قاعدة المتجر الحالية بعد أخذ نسخة احتياطية.
ALTER TABLE admins ADD COLUMN IF NOT EXISTS role ENUM('owner','assistant','orders','products') NOT NULL DEFAULT 'assistant';
ALTER TABLE admins ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1;
UPDATE admins SET role='owner' WHERE id=(SELECT first_id FROM (SELECT MIN(id) AS first_id FROM admins) AS first_admin);
CREATE TABLE IF NOT EXISTS store_settings(setting_key VARCHAR(80) PRIMARY KEY,setting_value TEXT NULL,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO store_settings(setting_key,setting_value) VALUES('store_name','الفخامة للشيشة والتبغ'),('store_description','اكتشف تجربة فاخرة لا مثيل لها'),('store_logo',''),('hero_image','');