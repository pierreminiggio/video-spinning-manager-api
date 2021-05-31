CREATE TABLE `spinned_content_video` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `spinned_content_video`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `spinned_content_video`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `spinned_content_video` ADD `content_id` INT NOT NULL AFTER `id`;
