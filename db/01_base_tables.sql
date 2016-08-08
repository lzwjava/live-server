SET NAMES utf8mb4;

SHOW VARIABLES LIKE 'character_set_%';
SHOW VARIABLES LIKE 'collation_%';

CREATE DATABASE `qulive`
  DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE `lives` (
  `liveId`   INT(11)       NOT NULL             AUTO_INCREMENT,
  `ownerId`  INT(11)       NOT NULL             DEFAULT 0,
  `rtmpKey`  VARCHAR(30)   NOT NULL             DEFAULT '',
  `subject`  VARCHAR(60)   NOT NULL             DEFAULT '',
  `coverUrl` VARCHAR(60)   NOT NULL             DEFAULT '',
  `amount`   INT           NOT NULL             DEFAULT 0,
  `detail`   VARCHAR(1023) NOT NULL             DEFAULT '',
  `status`   TINYINT(4)    NOT NULL             DEFAULT 0,
  `begin_ts` TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `end_ts`   TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `created`  TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`liveId`),
  UNIQUE KEY (`rtmpKey`),
  KEY `live_begin_ts` (`begin_ts`),
  KEY `live_status` (`status`),
  KEY `live_owner_id` (`ownerId`),
  FOREIGN KEY (`ownerId`) REFERENCES `users` (`userId`)
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


CREATE TABLE `charges` (
  `chargeId`  INT(11)     NOT NULL AUTO_INCREMENT,
  `orderNo`   VARCHAR(31) NOT NULL,
  `amount`    INT(11)     NOT NULL,
  `paid`      TINYINT(2)  NOT NULL DEFAULT '0',
  `creator`   VARCHAR(31) NOT NULL,
  `creatorIP` VARCHAR(63) NOT NULL,
  `created`   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`chargeId`),
  UNIQUE KEY `ORDER_NO_IDX` (`orderNo`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


CREATE TABLE `attendances` (
  `attendanceId` INT(11)     NOT NULL AUTO_INCREMENT,
  `userId`       VARCHAR(31) NOT NULL,
  `liveId`       INT(11)     NOT NULL,
  `chargeId`     INT(11)     NOT NULL,
  `created`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendanceId`),
  UNIQUE KEY `userId` (`userId`, `liveId`),
  KEY `liveId` (`liveId`),
  KEY `chargeId` (`chargeId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`),
  FOREIGN KEY (`chargeId`) REFERENCES `charges` (`chargeId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;