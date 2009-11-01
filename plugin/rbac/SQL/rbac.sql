DROP TABLE IF EXISTS `{$prefix}role_{$user_class_name}`;
CREATE TABLE `{$prefix}role_{$user_class_name}` (
    `{$user_class_name}_id` int(11) NOT NULL,
    `role_id` int(11) NOT NULL,
    KEY (`{$user_class_name}_id`),
    KEY (`role_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `{$prefix}roles`;
CREATE TABLE `{$prefix}roles` (
    `id` int(11) NOT NULL auto_increment,
    `role_name` varchar(255) NOT NULL,
    `actions` text NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
