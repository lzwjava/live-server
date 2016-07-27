SET NAMES utf8mb4;

SHOW VARIABLES LIKE 'character_set_%';
SHOW VARIABLES LIKE 'collation_%';

CREATE DATABASE `qulive`
  DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE `live` (
  `id`       INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `key`      VARCHAR(30)  NOT NULL             DEFAULT '',
  `subject`  VARCHAR(60)  NOT NULL             DEFAULT '',
  `status`   TINYINT(4)   NOT NULL             DEFAULT 0,
  `begin_ts` TIMESTAMP    NOT NULL             DEFAULT CURRENT_TIMESTAMP
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
