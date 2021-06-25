CREATE TABLE `spinned_content_video_editor_state` (
  `id` int NOT NULL,
  `video_id` int NOT NULL,
  `clips` text NOT NULL,
  `texts` text NOT NULL,
  `clip_maker_props` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `spinned_content_video_editor_state`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `spinned_content_video_editor_state`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

