CREATE TABLE `spinned_content_tiktok_upload` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `video_id` INT NOT NULL,
    `account_id` INT NOT NULL,
    `legend` VARCHAR(150) NOT NULL,
    `publish_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
