DROP TABLE IF EXISTS `oncall_weekly`;

CREATE TABLE `oncall_weekly` (
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
    `alert_id` varchar(255) NOT NULL,
    `range_start` int(10) unsigned NOT NULL,
    `range_end` int(10) unsigned NOT NULL,
    `timestamp` int(10) unsigned NOT NULL,
    `hostname` varchar(255) NOT NULL,
    `service` varchar(255) NOT NULL,
    `state` varchar(20) NOT NULL,
    `contact` varchar(255) NOT NULL,
    `output` text NOT NULL,
    `tag` varchar(255),
    `sleep_state` int(1) signed,
    `mtts` int(5) signed,
    `sleep_level` int(1) signed,
    `sleep_confidence` int(3) signed,
    `notes` text,
    PRIMARY KEY (`id`),
    KEY `alert_name` (`alert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `generic_weekly`;
CREATE TABLE `generic_weekly` (
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
    `report_id` varchar(255) NOT NULL,
    `range_start` int(10) unsigned NOT NULL,
    `range_end` int(10) unsigned NOT NULL,
    `timestamp` int(10) unsigned NOT NULL,
    `user` varchar(255) NOT NULL,
    `state` varchar(255) NOT NULL, 
    `report` text NOT NULL,
    PRIMARY KEY (`id`),
    KEY `report_name` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `meeting_notes`;
CREATE TABLE `meeting_notes` (
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
    `report_id` varchar(255) NOT NULL,
    `range_start` int(10) unsigned NOT NULL,
    `range_end` int(10) unsigned NOT NULL,
    `timestamp` int(10) unsigned NOT NULL,
    `user` varchar(255) NOT NULL,
    `notes` text NOT NULL,
    PRIMARY KEY (`id`),
    KEY `report_name` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE `user_profile` (
    `ldap_username` varchar(255) NOT NULL,
    `full_name` varchar(255) NOT NULL,
    `timezone` varchar(10) NOT NULL,
    `sleeptracking_provider` varchar(255) NOT NULL,
    `sleeptracking_settings` text NOT NULL,
    `team` varchar(128) DEFAULT NULL,
    PRIMARY KEY (`ldap_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

