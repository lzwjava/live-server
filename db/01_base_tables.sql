SET NAMES utf8mb4;

SHOW VARIABLES LIKE 'character_set_%';
SHOW VARIABLES LIKE 'collation_%';

CREATE DATABASE `qulive`
  DEFAULT CHARACTER SET utf8mb4;

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

CREATE TABLE `lives` (
  `liveId`          INT(11)       NOT NULL             AUTO_INCREMENT,
  `ownerId`         INT(11)       NOT NULL             DEFAULT 0,
  `rtmpKey`         VARCHAR(30)   NOT NULL             DEFAULT '',
  `subject`         VARCHAR(60)   NOT NULL             DEFAULT '',
  `coverUrl`        VARCHAR(80)   NOT NULL             DEFAULT '',
  `previewUrl`      VARCHAR(80)   NOT NULL             DEFAULT '',
  `amount`          INT           NOT NULL             DEFAULT 0,
  `maxPeople`       INT           NOT NULL             DEFAULT 0,
  `detail`          VARCHAR(8000) NOT NULL             DEFAULT '',
  `conversationId`  VARCHAR(30)   NOT NULL             DEFAULT '',
  `status`          TINYINT(4)    NOT NULL             DEFAULT 0,
  `attendanceCount` INT           NOT NULL             DEFAULT 0,
  `planTs`          TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `beginTs`         TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `endTs`           TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `created`         TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`         TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`liveId`),
  UNIQUE KEY (`rtmpKey`),
  KEY `live_status` (`status`),
  KEY `live_owner_id` (`ownerId`),
  FOREIGN KEY (`ownerId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `charges` (
  `chargeId`  INT(11)      NOT NULL AUTO_INCREMENT,
  `orderNo`   VARCHAR(31)  NOT NULL,
  `amount`    INT(11)      NOT NULL,
  `paid`      TINYINT(2)   NOT NULL DEFAULT '0',
  `channel`   VARCHAR(31)  NOT NULL DEFAULT '',
  `creator`   VARCHAR(31)  NOT NULL,
  `creatorIP` VARCHAR(63)  NOT NULL,
  `metaData`  VARCHAR(127) NOT NULL DEFAULT '',
  `created`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`chargeId`),
  UNIQUE KEY `ORDER_NO_IDX` (`orderNo`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `attendances` (
  `attendanceId` INT(11)     NOT NULL AUTO_INCREMENT,
  `userId`       INT(11)     NOT NULL,
  `liveId`       INT(11)     NOT NULL,
  `notified`     TINYINT(2)  NOT NULL DEFAULT 0,
  `orderNo`      VARCHAR(31) NOT NULL DEFAULT '',
  `created`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendanceId`),
  UNIQUE KEY `userId` (`userId`, `liveId`),
  KEY `liveId` (`liveId`),
  UNIQUE KEY `orderNo` (`orderNo`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `accounts` (
  `accountId` INT(11)   NOT NULL AUTO_INCREMENT,
  `userId`    INT(11)   NOT NULL,
  `balance`   INT(11)   NOT NULL DEFAULT 0,
  `created`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`accountId`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `transactions` (
  `transactionId` INT(11)     NOT NULL AUTO_INCREMENT,
  `userId`        INT(11)     NOT NULL,
  `orderNo`       VARCHAR(31) NOT NULL DEFAULT '',
  `amount`        INT(11)     NOT NULL DEFAULT 0,
  `oldBalance`    INT(11)     NOT NULL DEFAULT 0,
  `type`          TINYINT(4)  NOT NULL DEFAULT 0,
  `relatedId`     VARCHAR(31) NOT NULL DEFAULT '',
  `remark`        VARCHAR(60) NOT NULL DEFAULT '',
  `created`       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transactionId`),
  UNIQUE KEY `orderNo` (`orderNo`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `scanned_qrcodes` (
  `qrcodeId` INT(11)     NOT NULL AUTO_INCREMENT,
  `code`     VARCHAR(60) NOT NULL DEFAULT '',
  `type`     TINYINT     NOT NULL DEFAULT 0,
  `data`     VARCHAR(128)         DEFAULT '',
  `userId`   INT(11)     NOT NULL DEFAULT 0,
  `created`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`qrcodeId`),
  UNIQUE KEY (`code`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `sns_users` (
  `snsUserId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `openId`    VARCHAR(63)      NOT NULL DEFAULT '',
  `username`  VARCHAR(63)      NOT NULL DEFAULT '',
  `avatarUrl` VARCHAR(255)     NOT NULL DEFAULT '',
  `platform`  VARCHAR(10)      NOT NULL DEFAULT '',
  `userId`    INT(11)          NOT NULL DEFAULT 0,
  `created`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`snsUserId`),
  UNIQUE KEY (`openId`, `platform`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `shares` (
  `shareId`       INT(11)     NOT NULL AUTO_INCREMENT,
  `shareTs`       INT(11)     NOT NULL DEFAULT 0,
  `userId`        INT(11)     NOT NULL,
  `liveId`        INT(11)     NOT NULL,
  `channel`       VARCHAR(31) NOT NULL DEFAULT '',
  `useToDiscount` TINYINT(4)  NOT NULL DEFAULT 0,
  `created`       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`shareId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  PRIMARY KEY (`shareId`),
  UNIQUE KEY (`userId`, `liveId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `lives` ADD COLUMN `speakerIntro` VARCHAR(1000) NOT NULL DEFAULT ''
AFTER `maxPeople`;

ALTER TABLE `sns_users` ADD COLUMN `unionId` VARCHAR(63) NOT NULL DEFAULT ''
AFTER `openId`;

ALTER TABLE `users` ADD COLUMN `unionId` VARCHAR(63) NOT NULL DEFAULT ''
AFTER `avatarUrl`;

ALTER TABLE `users` MODIFY COLUMN `unionId` VARCHAR(63);

UPDATE users
SET unionId = NULL
WHERE unionId = '';

ALTER TABLE `users` ADD UNIQUE KEY `UNION_ID_IDX` (`unionId`);

ALTER TABLE `attendances`  ADD `wechatNotified` TINYINT(2) NOT NULL DEFAULT 0
AFTER `notified`;

ALTER TABLE `charges` ADD COLUMN `refunded` TINYINT(2) NOT NULL DEFAULT '0'
AFTER `paid`;

ALTER TABLE `attendances` ADD `videoNotified` TINYINT(0) NOT NULL DEFAULT 0
AFTER `wechatNotified`;

CREATE TABLE `coupons` (
  `couponId` INT(11)     NOT NULL             AUTO_INCREMENT,
  `liveId`   INT(11)     NOT NULL,
  `phone`    VARCHAR(40) NOT NULL             DEFAULT '',
  `userId`   INT(11)     NOT NULL             DEFAULT 0,
  `created`  TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`couponId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`),
  UNIQUE KEY (`phone`, `liveId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `recorded_videos` (
  `recordedVideoId` INT(11)     NOT NULL             AUTO_INCREMENT,
  `liveId`          INT(11)     NOT NULL,
  `fileName`        VARCHAR(60) NOT NULL             DEFAULT '',
  `endTs`           VARCHAR(20) NOT NULL             DEFAULT '',
  `transcoded`      TINYINT(2)  NOT NULL             DEFAULT 0,
  `transcodedTime`  TIMESTAMP                        DEFAULT CURRENT_TIMESTAMP,
  `created`         TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`         TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`recordedVideoId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`),
  UNIQUE KEY (`fileName`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `recorded_videos` ADD COLUMN `transcodedFileName` VARCHAR(60) NOT NULL             DEFAULT ''
AFTER `transcodedTime`;

CREATE TABLE `videos` (
  `videoId`  INT(11)     NOT NULL             AUTO_INCREMENT,
  `liveId`   INT(11)     NOT NULL,
  `title`    VARCHAR(60) NOT NULL             DEFAULT '',
  `fileName` VARCHAR(20) NOT NULL             DEFAULT '',
  `created`  TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`videoId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`),
  UNIQUE KEY (`liveId`, `fileName`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `lives`ADD COLUMN `needPay` TINYINT(1) NOT NULL DEFAULT 0
AFTER `previewUrl`;

UPDATE `lives`
SET `needPay` = 1
WHERE created < '2016-11-19 00:00:00';

ALTER TABLE `attendances` MODIFY COLUMN `orderNo` VARCHAR(31) DEFAULT NULL;