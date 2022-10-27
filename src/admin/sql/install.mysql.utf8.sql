DROP TABLE IF EXISTS `#__memberportal_member`;

CREATE TABLE `#__memberportal_member` (
	`member_code` VARCHAR(20) NOT NULL,
	`name_chi` VARCHAR(10) NOT NULL,
	PRIMARY KEY (`member_code`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
;