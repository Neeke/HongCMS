DROP TABLE IF EXISTS `hong_pcat`;

CREATE TABLE IF NOT EXISTS `hong_pcat` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_id` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  `show_sub` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL DEFAULT '',
  `name_en` varchar(255) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `keywords_en` varchar(255) NOT NULL DEFAULT '',
  `desc_cn` text NOT NULL,
  `desc_en` text NOT NULL,
  `counts` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `p_id` (`p_id`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `hong_product`;

CREATE TABLE IF NOT EXISTS `hong_product` (
  `pro_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  `is_best` tinyint(1) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(64) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `price` VARCHAR(36) NOT NULL default '',
  `price_en` VARCHAR(36) NOT NULL default '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_en` varchar(255) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `keywords_en` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `content_en` text NOT NULL,
  `clicks` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pro_id`),
  KEY `sort` (`sort`),
  KEY `created` (`created`),
  KEY `cat_id` (`cat_id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `hong_gimage`;

CREATE TABLE IF NOT EXISTS `hong_gimage` (
  `g_id` int(11) NOT NULL auto_increment,
  `pro_id` int(30) NOT NULL default '0',
  `is_show` TINYINT(1) NOT NULL default '1',
  `path` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`g_id`),
  KEY `pro_id` (`pro_id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `hong_acat`;

CREATE TABLE IF NOT EXISTS `hong_acat` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_id` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  `show_sub` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL DEFAULT '',
  `name_en` varchar(255) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `keywords_en` varchar(255) NOT NULL DEFAULT '',
  `desc_cn` text NOT NULL,
  `desc_en` text NOT NULL,
  `counts` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `p_id` (`p_id`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `hong_article`;

CREATE TABLE IF NOT EXISTS `hong_article` (
  `a_id` int(30) NOT NULL auto_increment,
  `sort` int(30) NOT NULL default '0',
  `cat_id` int(11) NOT NULL default '0',
  `is_show` tinyint(1) NOT NULL default '0',
  `is_best` tinyint(1) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL default '0',
  `username` varchar(64) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `title_en` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `content_en` text NOT NULL,
  `keywords` varchar(255) NOT NULL default '',
  `keywords_en` varchar(255) NOT NULL default '',
  `clicks` int(11) NOT NULL default '0',
  `created` int(11) NOT NULL default '0',
  PRIMARY KEY  (`a_id`),
  KEY `sort` (`sort`),
  KEY `created` (`created`),
  KEY `cat_id` (`cat_id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `hong_sessions`;

CREATE TABLE IF NOT EXISTS `hong_sessions` (
  `sessionid` char(64) NOT NULL default '',
  `userid` int(11) NOT NULL default '0',
  `ipaddress` varchar(64) NOT NULL default '',
  `useragent` varchar(255) NOT NULL default '',
  `created` int(11) NOT NULL default '0',
  `admin` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`sessionid`),
  KEY `userid` (`userid`),
  KEY `created` (`created`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `hong_admin`;

CREATE TABLE IF NOT EXISTS `hong_admin` (
  `userid` int(11) NOT NULL auto_increment,
  `activated` tinyint(1) NOT NULL default '0',
  `username` varchar(64) NOT NULL default '',
  `password` varchar(64) NOT NULL default '',
  `joindate` int(11) NOT NULL default '0',
  `lastdate` int(11) NOT NULL default '0',
  `joinip` varchar(64) NOT NULL default '',
  `lastip` varchar(64) NOT NULL default '',
  `loginnum` int(11) NOT NULL default '0',
  `nickname` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`userid`),
  KEY `joindate` (`joindate`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `hong_content`;

CREATE TABLE IF NOT EXISTS `hong_content` (
  `c_id` int(11) NOT NULL auto_increment,
  `title` VARCHAR(255) NOT NULL default '',
  `title_en` VARCHAR(255) NOT NULL default '',
  `content` text NOT NULL,
  `content_en` text NOT NULL,
  `keywords` varchar(255) NOT NULL default '',
  `keywords_en` varchar(255) NOT NULL default '',
  `created` int(11) NOT NULL default '0',
  `r_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`c_id`),
  KEY `r_id` (`r_id`),
  KEY `created` (`created`)
) ENGINE=MyISAM;


INSERT INTO `hong_content`  (`c_id`, `title`, `title_en`, `content`, `content_en`, `keywords`, `keywords_en`, `created`, `r_id`) VALUES 
(1,'关于我们','About Us','请在后台管理中自定义关于我们的详细内容.','please edit your content.','关于,我们','about us','1265951785', 1),
(2,'联系我们','Contact Us','请在后台管理中自定义联系我们的详细内容.','please edit your content.','联系,我们','contact us','1265951785', 2),
(3,'首页常态内容','Homepage content','请在后台管理中自定义首页常态内容.','please edit your homepage content.','hongcms中英文企业网站系统','hongcms,website system','1265951785', 3),
(4,'第一个公司','The first Company','请在后台管理常态内容中自定义第一个公司详细介绍.','please edit The first Company content on back-end.','hongcms,website system','hongcms,website system','1265951785', 11),
(5,'第二个公司','The second Company','请在后台管理常态内容中自定义第二个公司详细介绍.','please edit The second Company content on back-end.','hongcms,website system','hongcms,website system','1265951785', 12),
(6,'第三个公司','The third Company','请在后台管理常态内容中自定义第三个公司详细介绍.','please edit The third Company content on back-end.','hongcms,website system','hongcms,website system','1265951785', 13),
(7,'企业文化','Our Culture','请在后台管理常态内容中自定义企业文化详细内容.','please edit Our Culture content on back-end.','hongcms,website system','hongcms,website system','1265951785', 14),
(8,'组织结构','Organization','请在后台管理常态内容中自定义组织结构详细内容.','please edit Organization content on back-end.','hongcms,website system','hongcms,website system','1265951785', 15);


DROP TABLE IF EXISTS `hong_news`;

CREATE TABLE IF NOT EXISTS `hong_news` (
  `n_id` int(11) NOT NULL auto_increment,
  `sort` int(11) NOT NULL default '0',
  `is_show` TINYINT(1) NOT NULL default '1',
  `title` varchar(255) NOT NULL default '',
  `title_en` varchar(255) NOT NULL default '',
  `linkurl` varchar(255) NOT NULL default '',
  `linkurl_en` varchar(255) NOT NULL default '',
  `keywords` varchar(255) NOT NULL default '',
  `keywords_en` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `content_en` text NOT NULL,
  `clicks` int(30) NOT NULL default '0',
  `created` int(11) NOT NULL default '0',
  PRIMARY KEY  (`n_id`),
  KEY `sort` (`sort`),
  KEY `created` (`created`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `hong_vvc`;

CREATE TABLE IF NOT EXISTS `hong_vvc` (
  `vvcid` int(30) NOT NULL auto_increment,
  `code` varchar(9) NOT NULL default '',
  `created` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vvcid`),
  KEY `created` (`created`)
) ENGINE=MyISAM;