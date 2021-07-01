CREATE TABLE `spinned_content_upload_status` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `upload_type` VARCHAR(30) NOT NULL,
    `upload_id` INT NOT NULL,
    `finished_at` DATETIME NULL,
    `remote_url` TEXT NULL,
    `failed_at` DATETIME NULL,
    `fail_reason` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
