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


DROP TABLE IF EXISTS `#__memberportal_cell_groups`;

CREATE TABLE `#__memberportal_cell_groups` (
	`name` VARCHAR(10) NOT NULL,
	`zone` VARCHAR(10),
	`district` VARCHAR(10),
	`start_date` DATE,
	`end_date` DATE,
	PRIMARY KEY (`name`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_cell_schedule`;

CREATE TABLE `#__memberportal_cell_schedule` (
	`year` INT(11) NOT NULL,
	`week` INT(11) NOT NULL,
	`week_start` DATE,
	PRIMARY KEY (`year`, `week`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_member_attrs`;

CREATE TABLE `#__memberportal_member_attrs` (
	`member_code` VARCHAR(20) NOT NULL,
	`cell_group_name` VARCHAR(10) NOT NULL,
	`cell_role` VARCHAR(20) NOT NULL,
	`member_category` VARCHAR(20),
	`start_date` DATE,
	`end_date` DATE, 
	PRIMARY KEY (`member_code`, `start_date`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_attendance_ceremony`;

CREATE TABLE `#__memberportal_attendance_ceremony` (
	`date` DATE NOT NULL,
	`member_code` VARCHAR(20) NOT NULL,
	PRIMARY KEY (`date`, `member_code`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_attendance_cell`;

CREATE TABLE `#__memberportal_attendance_cell` (
	`date` DATE NOT NULL,
	`member_code` VARCHAR(20),
	`visitor_name` VARCHAR(20),
	`cell_group_name` VARCHAR(10) NOT NULL,
	`event_type` VARCHAR(10) NOT NULL,
	PRIMARY KEY (`date`, `cell_group_name`, `member_code`, `visitor_name`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_offerings`;

CREATE TABLE `#__memberportal_offerings` (
	`date` DATE NOT NULL,
	`member_code` VARCHAR(20) NOT NULL,
	`num_offerings` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`date`, `member_code`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_serving_posts`;

CREATE TABLE `#__memberportal_serving_posts` (
	`member_code` VARCHAR(20) NOT NULL,
	`name` VARCHAR(10) NOT NULL,
	`post` VARCHAR(20) NOT NULL,
	`start_date` DATE,
	`end_date` DATE, 
	PRIMARY KEY (`member_code`, `post`, `start_date`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_courses`;

CREATE TABLE `#__memberportal_courses` (
	`member_code` VARCHAR(20) NOT NULL,
	`name` VARCHAR(10) NOT NULL,
	`course` VARCHAR(50) NOT NULL,
	`start_date` DATE,
	`end_date` DATE,
	`status` VARCHAR(20) NOT NULL,
	PRIMARY KEY (`member_code`, `course`, `start_date`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_uploaded_files`;

CREATE TABLE `#__memberportal_uploaded_files` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`uploaded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`uploaded_by` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`orig_file_name` VARCHAR(255) NOT NULL,
	`saved_file_name` VARCHAR(255) NOT NULL,
	`import_result` TEXT,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_offering_details`;

CREATE TABLE `#__memberportal_offering_details` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`date` DATE NOT NULL,
	`member_code` VARCHAR(20) NOT NULL,
	`payment_method` VARCHAR(20) NOT NULL,
	`cheque_no` VARCHAR(20) NOT NULL,
	`receipt_type` VARCHAR(20) NOT NULL,
	`offering_type` VARCHAR(20) NOT NULL,
	`offering_amount` TEXT NOT NULL,
	`remarks` TEXT,
	`upload_id` INT(11) NOT NULL,
	`line` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;


DROP TABLE IF EXISTS `#__memberportal_offering_details_uploaded_files`;

CREATE TABLE `#__memberportal_offering_details_uploaded_files` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`uploaded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`uploaded_by` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`orig_file_name` VARCHAR(255) NOT NULL,
	`saved_file_name` VARCHAR(255) NOT NULL,
	`import_result` TEXT,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;