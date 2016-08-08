SET NAMES utf8mb4;

SHOW VARIABLES LIKE 'character_set_%';
SHOW VARIABLES LIKE 'collation_%';

CREATE DATABASE `qulive`
  DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE `lives` (
  `id`       INT(11)       NOT NULL             AUTO_INCREMENT,
  `ownerId`  INT(11)       NOT NULL             DEFAULT 0,
  `key`      VARCHAR(30)   NOT NULL             DEFAULT '',
  `subject`  VARCHAR(60)   NOT NULL             DEFAULT '',
  `coverUrl` VARCHAR(60)   NOT NULL             DEFAULT '',
  `amount`   INT           NOT NULL             DEFAULT 0,
  `detail`   VARCHAR(1023) NOT NULL             DEFAULT '',
  `status`   TINYINT(4)    NOT NULL             DEFAULT 0,
  `begin_ts` TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `end_ts`   TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `created`  TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`key`),
  KEY `live_begin_ts` (`begin_ts`),
  KEY `live_status` (`status`),
  KEY `live_owner_id` (`ownerId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


CREATE TABLE `users` (
  `userId`              INT(11)      NOT NULL AUTO_INCREMENT,
  `username`            VARCHAR(127) NOT NULL DEFAULT '',
  `mobilePhoneNumber`   VARCHAR(63)  NOT NULL DEFAULT '',
  `avatarUrl`           VARCHAR(255) NOT NULL DEFAULT '',
  `sessionToken`        VARCHAR(127) NOT NULL DEFAULT '',
  `sessionTokenCreated` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `password`            VARCHAR(127) NOT NULL DEFAULT '',
  `created`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `NAME_IDX` (`username`),
  UNIQUE KEY `PHONE_IDX` (`mobilePhoneNumber`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;