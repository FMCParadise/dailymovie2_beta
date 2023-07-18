CREATE TABLE `users` (
  `id` int PRIMARY KEY,
  `name` varchar(255),
  `first_name` varchar(255),
  `email` varchar(255) UNIQUE,
  `password` varchar(255),
  `role` tinyint(1),
  `avatar` varchar(255),
  `rgpd` tinyint(1),
  `created_at` datetime
);

CREATE TABLE `posts` (
  `id` int PRIMARY KEY,
  `users_id` int,
  `title` varchar(255),
  `content` text,
  `image` varchar(255),
  `slug` varchar(255) UNIQUE,
  `created_at` datetime
);

CREATE TABLE `categories` (
  `id` int PRIMARY KEY,
  `name` varchar(50) UNIQUE,
  `slug` varchar(255) UNIQUE
);

CREATE TABLE `posts_categories` (
  `posts_id` int,
  `categories_id` int,
  PRIMARY KEY (`posts_id`, `categories_id`)
);

ALTER TABLE `posts` ADD FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);

ALTER TABLE `posts_categories` ADD FOREIGN KEY (`posts_id`) REFERENCES `posts` (`id`);

ALTER TABLE `posts_categories` ADD FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`);
