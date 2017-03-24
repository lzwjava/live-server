SET NAMES utf8mb4;

SHOW VARIABLES LIKE 'character_set_%';
SHOW VARIABLES LIKE 'collation_%';

CREATE DATABASE `qulive`
  DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE `users` (
  `userId`              INT(11)      NOT NULL AUTO_INCREMENT,
  `username`            VARCHAR(127) NOT NULL DEFAULT '',
  `mobilePhoneNumber`   VARCHAR(63)           DEFAULT NULL,
  `avatarUrl`           VARCHAR(255) NOT NULL DEFAULT '',
  `unionId`             VARCHAR(63)           DEFAULT NULL,
  `sessionToken`        VARCHAR(127) NOT NULL DEFAULT '',
  `sessionTokenCreated` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `password`            VARCHAR(127) NOT NULL DEFAULT '',
  `wechatSubscribe`     TINYINT(2)   NOT NULL DEFAULT 0,
  `liveSubscribe`       TINYINT(2)            DEFAULT 0,
  `incomeSubscribe`     TINYINT(2)            DEFAULT 1,
  `created`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `NAME_IDX` (`username`),
  UNIQUE KEY `PHONE_IDX` (`mobilePhoneNumber`),
  UNIQUE KEY `UNION_ID_IDX` (`unionId`)
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
  `needPay`         TINYINT(1)    NOT NULL             DEFAULT 0,
  `amount`          INT           NOT NULL             DEFAULT 0,
  `maxPeople`       INT           NOT NULL             DEFAULT 0,
  `speakerIntro`    VARCHAR(1000) NOT NULL             DEFAULT '',
  `detail`          VARCHAR(8000) NOT NULL             DEFAULT '',
  `notice`          VARCHAR(300)  NOT NULL             DEFAULT '',
  `shareIcon`       TINYINT(2)    NOT NULL             DEFAULT 0,
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
  `refunded`  TINYINT(2)   NOT NULL DEFAULT '0',
  `channel`   VARCHAR(31)  NOT NULL DEFAULT '',
  `creator`   VARCHAR(31)  NOT NULL,
  `creatorIP` VARCHAR(63)  NOT NULL,
  `metaData`  VARCHAR(300) NOT NULL DEFAULT '',
  `prepayId`  VARCHAR(50)  NOT NULL DEFAULT '',
  `remark`    VARCHAR(300) NOT NULL DEFAULT '',
  `created`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`chargeId`),
  UNIQUE KEY `ORDER_NO_IDX` (`orderNo`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `attendances` (
  `attendanceId`   INT(11)    NOT NULL AUTO_INCREMENT,
  `userId`         INT(11)    NOT NULL,
  `liveId`         INT(11)    NOT NULL,
  `fromUserId`     INT(11)             DEFAULT NULL,
  `firstNotified`  TINYINT(2)          DEFAULT 0,
  `preNotified`    TINYINT(2)          DEFAULT 0,
  `notified`       TINYINT(2) NOT NULL DEFAULT 0,
  `wechatNotified` TINYINT(2) NOT NULL DEFAULT 0,
  `videoNotified`  TINYINT(2) NOT NULL DEFAULT 0,
  `orderNo`        VARCHAR(31)         DEFAULT NULL,
  `created`        TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`        TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendanceId`),
  UNIQUE KEY `userId` (`userId`, `liveId`),
  KEY `liveId` (`liveId`),
  UNIQUE KEY `orderNo` (`orderNo`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`),
  FOREIGN KEY (`fromUserId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `accounts` (
  `accountId` INT(11)   NOT NULL AUTO_INCREMENT,
  `userId`    INT(11)   NOT NULL,
  `balance`   INT(11)   NOT NULL DEFAULT 0,
  `income`    INT(11)   NOT NULL DEFAULT 0,
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

CREATE TABLE `sns_users` (
  `snsUserId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `openId`    VARCHAR(63)      NOT NULL DEFAULT '',
  `unionId`   VARCHAR(63)      NOT NULL DEFAULT '',
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
  UNIQUE KEY (`userId`, `liveId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

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
  `recordedVideoId`    INT(11)     NOT NULL             AUTO_INCREMENT,
  `liveId`             INT(11)     NOT NULL,
  `fileName`           VARCHAR(60) NOT NULL             DEFAULT '',
  `endTs`              VARCHAR(20) NOT NULL             DEFAULT '',
  `transcoded`         TINYINT(2)  NOT NULL             DEFAULT 0,
  `transcodedTime`     TIMESTAMP                        DEFAULT CURRENT_TIMESTAMP,
  `transcodedFileName` VARCHAR(60) NOT NULL             DEFAULT '',
  `created`            TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`            TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`recordedVideoId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`),
  UNIQUE KEY (`fileName`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `recorded_videos` CHANGE `endTs` `beginTs` VARCHAR(20) NOT NULL  DEFAULT '';

CREATE TABLE `videos` (
  `videoId`  INT(11)     NOT NULL             AUTO_INCREMENT,
  `liveId`   INT(11)     NOT NULL,
  `title`    VARCHAR(60) NOT NULL             DEFAULT '',
  `type`     VARCHAR(10)                      DEFAULT 'mp4',
  `fileName` VARCHAR(20) NOT NULL             DEFAULT '',
  `created`  TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`videoId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`),
  UNIQUE KEY (`liveId`, `fileName`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `rewards` (
  `rewardId` INT(11)     NOT NULL AUTO_INCREMENT,
  `userId`   INT(11)     NOT NULL,
  `liveId`   INT(11)     NOT NULL,
  `orderNo`  VARCHAR(31) NOT NULL DEFAULT '',
  `created`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rewardId`),
  UNIQUE KEY `orderNo` (`orderNo`),
  UNIQUE KEY (`userId`, `liveId`, `created`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `live_views` (
  `liveViewId` INT(11)     NOT NULL             AUTO_INCREMENT,
  `userId`     INT(11)     NOT NULL,
  `liveId`     INT(11)     NOT NULL,
  `platform`   VARCHAR(20) NOT NULL             DEFAULT '',
  `liveStatus` TINYINT(4)  NOT NULL             DEFAULT 0,
  `created`    TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `ended`      TINYINT(2)  NOT NULL             DEFAULT 0,
  `endTs`      TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  `updated`    TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`liveViewId`),
  UNIQUE KEY (`userId`, `liveId`, `created`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  FOREIGN KEY (`liveId`) REFERENCES `lives` (`liveId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `staffs` (
  `staffId` INT(11)   NOT NULL AUTO_INCREMENT,
  `userId`  INT(11)   NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`staffId`),
  UNIQUE KEY (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `applications` (
  `applicationId`  INT(11)      NOT NULL AUTO_INCREMENT,
  `userId`         INT(11)      NOT NULL,
  `name`           VARCHAR(30)  NOT NULL DEFAULT '',
  `wechatAccount`  VARCHAR(30)  NOT NULL DEFAULT '',
  `socialAccount`  VARCHAR(200) NOT NULL DEFAULT '',
  `introduction`   VARCHAR(500) NOT NULL DEFAULT '',
  `status`         TINYINT(4)   NOT NULL DEFAULT 0,
  `reviewRemark`   VARCHAR(100) NOT NULL DEFAULT '',
  `reviewNotified` TINYINT(2)   NOT NULL DEFAULT 0,
  `created`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`applicationId`),
  UNIQUE KEY (`userId`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `packets` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `packetId`    VARCHAR(20)  NOT NULL DEFAULT '',
  `userId`      INT(11)      NOT NULL,
  `totalAmount` INT          NOT NULL DEFAULT 0,
  `totalCount`  INT          NOT NULL DEFAULT 0,
  `orderNo`     VARCHAR(31)           DEFAULT NULL,
  `wishing`     VARCHAR(100) NOT NULL DEFAULT 0,
  `balance`     INT          NOT NULL DEFAULT 0,
  `remainCount` INT          NOT NULL DEFAULT 0,
  `created`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`packetId`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `user_packets` (
  `userPacketId` INT(11)     NOT NULL AUTO_INCREMENT,
  `packetId`     VARCHAR(20) NOT NULL DEFAULT '',
  `userId`       INT(11)     NOT NULL,
  `amount`       INT(11)     NOT NULL DEFAULT 0,
  `sended`       TINYINT(2)  NOT NULL DEFAULT 0,
  `created`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userPacketId`),
  FOREIGN KEY (`packetId`) REFERENCES `packets` (`packetId`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  UNIQUE KEY (`packetId`, `userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `topics` (
  `topicId` INT(11)      NOT NULL             AUTO_INCREMENT,
  `name`    VARCHAR(127) NOT NULL,
  PRIMARY KEY (`topicId`),
  UNIQUE KEY `TOPIC_NAME_IDX` (`name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE `subscribes` (
  `subscribeId` INT(11)   NOT NULL             AUTO_INCREMENT,
  `userId`      INT(11)   NOT NULL,
  `topicId`     INT(11)   NOT NULL,
  `created`     TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`subscribeId`),
  UNIQUE KEY `user_topic` (`userId`, `topicId`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  FOREIGN KEY (`topicId`) REFERENCES `topics` (`topicId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `lives` ADD COLUMN `topicId` INT(11)
AFTER `status`;

ALTER TABLE `lives` ADD FOREIGN KEY (`topicId`) REFERENCES `topics` (`topicId`);

CREATE TABLE `withdraws` (
  `withdrawId` INT(11)   NOT NULL AUTO_INCREMENT,
  `userId`     INT(11)   NOT NULL,
  `amount`     INT(11)   NOT NULL DEFAULT 0,
  `status`     INT(11)   NOT NULL DEFAULT 0,
  `created`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`withdrawId`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

INSERT INTO `users` (`userId`, `username`, `mobilePhoneNumber`, `avatarUrl`)
VALUES (100000, '系统', NULL, 'http://i.quzhiboapp.com/icon108.jpg');

INSERT INTO `accounts` (`userId`, `balance`) VALUE (100000, 0);

CREATE TABLE `jobs` (
  `jobId`     INT(11)      NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(30)  NOT NULL DEFAULT '',
  `params`    VARCHAR(300) NOT NULL DEFAULT '',
  `status`    TINYINT      NOT NULL DEFAULT 0,
  `triggerTs` INT(11)      NOT NULL DEFAULT 0,
  `report`    VARCHAR(100) NOT NULL DEFAULT '',
  `created`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`jobId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `users` ADD COLUMN `incomeSubscribe` TINYINT(2) DEFAULT 1
AFTER `liveSubscribe`;


CREATE TABLE `wechat_events` (
  `wechatEventId` INT(11)      NOT NULL AUTO_INCREMENT,
  `eventType`     VARCHAR(30)  NOT NULL DEFAULT '',
  `eventData`     VARCHAR(100) NOT NULL DEFAULT '',
  `openId`        VARCHAR(63)  NOT NULL DEFAULT '',
  `userId`        INT(11)               DEFAULT NULL,
  `created`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`wechatEventId`),
  FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
