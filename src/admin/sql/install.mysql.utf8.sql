DROP TABLE IF EXISTS `#__memberportal_members`;

CREATE TABLE `#__memberportal_members` (
	`member_code` VARCHAR(20) NOT NULL,
	`name_chi` VARCHAR(10) NOT NULL,
	PRIMARY KEY (`member_code`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_uploaded_files`;

CREATE TABLE `#__memberportal_uploaded_files` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`uploaded` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`uploaded_by` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`orig_file_name` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;