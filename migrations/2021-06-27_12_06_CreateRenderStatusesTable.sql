CREATE TABLE `channel_storage`.`spinned_content_video_render_status` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `video_id` INT NOT NULL,
    `finished_at` DATETIME NULL,
    `file_path` VARCHAR(255) NULL,
    `failed_at` DATETIME NULL,
    `fail_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;