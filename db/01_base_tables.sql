SET NAMES utf8mb4;

SHOW VARIABLES LIKE 'character_set_%';
SHOW VARIABLES LIKE 'collation_%';

CREATE DATABASE `qulive`
  DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE `live` (
  `id`       INT UNSIGNED NOT NULL             AUTO_INCREMENT,
  `key`      VARCHAR(30)  NOT NULL             DEFAULT '',
  `subject`  VARCHAR(60)  NOT NULL             DEFAULT '',
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
