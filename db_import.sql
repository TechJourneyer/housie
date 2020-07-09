CREATE TABLE `game_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(256) DEFAULT NULL,
  `group_session_id` varchar(256) NOT NULL,
  `host_type` enum('user','guest') NOT NULL DEFAULT 'user',
  `host_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_active_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `announced_numbers` text,
  `closed_criterias` varchar(256) DEFAULT NULL,
  `dividends` text,
  `ticket_price` decimal(10,2) NOT NULL DEFAULT '10.00',
  `status` varchar(25) NOT NULL DEFAULT 'booking_open' COMMENT 'booking_open,game_start,game_over',
  `session_end` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `game_session_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('user','guest') NOT NULL DEFAULT 'user',
  `back_out` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 : no 1 : yes',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_session_id` (`game_session_id`),
  CONSTRAINT `game_session_users_ibfk_2` FOREIGN KEY (`game_session_id`) REFERENCES `game_session` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `guests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `session_id` varchar(256) NOT NULL,
  `online` varchar(256) NOT NULL DEFAULT '1' COMMENT '0:offline : 1 online',
  `last_online` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `group_session_id` varchar(256) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;


DELIMITER ;;

CREATE TRIGGER `guests_au` AFTER UPDATE ON `guests` FOR EACH ROW
UPDATE game_session
SET session_end = 1
WHERE TIMESTAMPDIFF(MINUTE, last_active_at , current_timestamp()) > 15;;

DELIMITER ;

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_session_users_id` int(11) NOT NULL,
  `ticket_closed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:closed',
  `ticket_numbers` varchar(256) NOT NULL,
  `marked_numbers` varchar(256) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_session_users_id` (`game_session_users_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`game_session_users_id`) REFERENCES `game_session_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `username` varchar(20) DEFAULT NULL,
  `email_id` varchar(30) NOT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `photo_url` text,
  `uid` varchar(256) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0:inactive : 1 active',
  `online` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:offline : 1 online',
  `session_id` text,
  `group_session_id` varchar(256) DEFAULT NULL,
  `last_online` datetime DEFAULT NULL,
  `added_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;


DELIMITER ;;

CREATE TRIGGER `users_au` AFTER UPDATE ON `users` FOR EACH ROW
UPDATE game_session
SET session_end = 1
WHERE TIMESTAMPDIFF(MINUTE, last_active_at , current_timestamp()) > 15;;

DELIMITER ;

CREATE TABLE `winners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `criteria` varchar(256) NOT NULL,
  `announced_number` tinyint(3) NOT NULL,
  `prize` decimal(10,2) NOT NULL,
  `ticket_claim_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 :claimed 2 claim approved 3 : claim rejected',
  `claimed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_criteria` (`criteria`,`ticket_id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `winners_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
