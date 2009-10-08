DROP TABLE IF EXISTS `{$prefix}parameters`;
CREATE TABLE IF NOT EXISTS `{$prefix}parameters` (
  `id` int(11) NOT NULL auto_increment,
  `param_key` varchar(255) collate utf8_unicode_ci NOT NULL,
  `param_val` varchar(255) collate utf8_unicode_ci NOT NULL,
  `param_type` varchar(255) collate utf8_unicode_ci NOT NULL default 'string',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
