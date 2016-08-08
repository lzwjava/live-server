SET NAMES utf8mb4;

SHOW VARIABLES LIKE 'character_set_%';
SHOW VARIABLES LIKE 'collation_%';

CREATE DATABASE `qulive`
  DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE `live` (
  `id`       INT UNSIGNED NOT NULL             AUTO_INCREMENT,
  `key`      VARCHAR(30)  NOT NULL             DEFAULT '',
  `subject`  VARCHAR(60)  NOT NULL             DEFAULT '',
  `coverUrl` VARCHAR(60)  NOT NULL             DEFAULT '',
  `status`   TINYINT(4)   NOT NULL             DEFAULT 0,
  `begin_ts` TIMESTAMP    NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `end_ts`   TIMESTAMP    NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`key`),
  KEY `live_begin_ts` (`begin_ts`),
  KEY `live_status` (`status`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


CREATE TABLE `users` (
  `userId`              INT(11)      NOT NULL AUTO_INCREMENT,
  `username`            VARCHAR(127) NOT NULL,
  `mobilePhoneNumber`   VARCHAR(63)  NOT NULL,
  `avatarUrl`           VARCHAR(255) NOT NULL,
  `sessionToken`        VARCHAR(127) NOT NULL,
  `sessionTokenCreated` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `password`            VARCHAR(127) NOT NULL,
  `created`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `NAME_IDX` (`username`),
  UNIQUE KEY `PHONE_IDX` (`mobilePhoneNumber`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;