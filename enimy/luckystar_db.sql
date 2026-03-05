-- =============================================
-- LuckyStar Lottery – Database
-- Database: luckystar_db
-- Import this file into phpMyAdmin
-- =============================================

CREATE DATABASE IF NOT EXISTS `luckystar_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `luckystar_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `fullname`   VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lottery_purchases` (
  `id`        INT(11)        NOT NULL AUTO_INCREMENT,
  `user_id`   INT(11)        NOT NULL,
  `number`    CHAR(6)        NOT NULL,
  `units`     INT(11)        NOT NULL DEFAULT 1,
  `price`     DECIMAL(10,2)  NOT NULL,
  `bought_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user` (`user_id`),
  CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `winning_numbers` (
  `id`         INT(11)   NOT NULL AUTO_INCREMENT,
  `number`     CHAR(6)   NOT NULL,
  `draw_date`  DATE      NOT NULL DEFAULT (CURDATE()),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `winning_numbers` (`number`, `draw_date`) VALUES ('456789', CURDATE());
