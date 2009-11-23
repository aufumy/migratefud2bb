CREATE TABLE `map_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `fud_users_id` INT UNSIGNED NOT NULL,
  `bb_users_ID` INT UNSIGNED NOT NULL,
  KEY `fud_users_id` (`fud_users_id`)
) ENGINE=MyISAM;

CREATE TABLE `map_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `fud_msg_id` INT UNSIGNED NOT NULL,
  `fud_thread_id` INT UNSIGNED NOT NULL,
  `fud_file_id` INT UNSIGNED NOT NULL,
  `bb_posts_post_id` INT UNSIGNED NOT NULL,
  KEY `fud_msg_id` (`fud_msg_id`)
) ENGINE=MyISAM;
