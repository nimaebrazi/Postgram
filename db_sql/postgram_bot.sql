--
-- Table structure for table `add_admins`
--

CREATE TABLE IF NOT EXISTS `add_admins` (
`id` int(10) unsigned NOT NULL,
  `creator_chat_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_chat_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `channel_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `admin_username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `channels`
--

CREATE TABLE IF NOT EXISTS `channels` (
`id` int(10) unsigned NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telegram_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `channel_user`
--

CREATE TABLE IF NOT EXISTS `channel_user` (
`id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `channel_id` int(10) unsigned DEFAULT NULL,
  `add_admins_id` int(10) unsigned DEFAULT NULL,
  `active` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `charges`
--

CREATE TABLE IF NOT EXISTS `charges` (
`id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `reference_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
`id` int(10) unsigned NOT NULL,
  `chat_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `channel_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `year` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `month` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hour` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `minute` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_accepted` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `remove_admins`
--

CREATE TABLE IF NOT EXISTS `remove_admins` (
`id` int(10) unsigned NOT NULL,
  `creator_chat_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_chat_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_id` int(255) NOT NULL,
  `channel_id` int(255) NOT NULL,
  `added_by` int(10) unsigned DEFAULT NULL,
  `removed_by` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
`id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`id` int(10) unsigned NOT NULL,
  `chat_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rule` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT NULL,
  `added_by` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `add_admins`
--
ALTER TABLE `add_admins`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channel_user`
--
ALTER TABLE `channel_user`
 ADD PRIMARY KEY (`id`), ADD KEY `channel_user_user_id_foreign` (`user_id`), ADD KEY `channel_user_add_admins_id_foreign` (`add_admins_id`), ADD KEY `channel_user_channel_id_user_id_add_admins_id_index` (`channel_id`,`user_id`,`add_admins_id`);

--
-- Indexes for table `charges`
--
ALTER TABLE `charges`
 ADD PRIMARY KEY (`id`), ADD KEY `charges_user_id_index` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
 ADD PRIMARY KEY (`id`), ADD KEY `posts_channel_id_foreign` (`channel_id`), ADD KEY `posts_user_id_channel_id_index` (`user_id`,`channel_id`);

--
-- Indexes for table `remove_admins`
--
ALTER TABLE `remove_admins`
 ADD PRIMARY KEY (`id`), ADD KEY `remove_admins_removed_by_foreign` (`removed_by`), ADD KEY `remove_admins_added_by_removed_by_index` (`added_by`,`removed_by`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
 ADD PRIMARY KEY (`id`), ADD KEY `transactions_user_id_index` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD KEY `users_added_by_index` (`added_by`);

--
-- AUTO_INCREMENT for table `add_admins`
--
ALTER TABLE `add_admins`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `channel_user`
--
ALTER TABLE `channel_user`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `charges`
--
ALTER TABLE `charges`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `remove_admins`
--
ALTER TABLE `remove_admins`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `channel_user`
--
ALTER TABLE `channel_user`
ADD CONSTRAINT `channel_user_add_admins_id_foreign` FOREIGN KEY (`add_admins_id`) REFERENCES `add_admins` (`id`),
ADD CONSTRAINT `channel_user_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `channel_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `charges`
--
ALTER TABLE `charges`
ADD CONSTRAINT `charges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
ADD CONSTRAINT `posts_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remove_admins`
--
ALTER TABLE `remove_admins`
ADD CONSTRAINT `remove_admins_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `add_admins` (`id`),
ADD CONSTRAINT `remove_admins_removed_by_foreign` FOREIGN KEY (`removed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
ADD CONSTRAINT `users_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `add_admins` (`id`);
