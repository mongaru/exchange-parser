--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `ID_activity` int(11) NOT NULL auto_increment COMMENT 'Activity unique ID',
  `ID_object` int(11) NOT NULL COMMENT 'Object ID for which we are logging activity (Yield, Discussion, People, etc.)',
  `ID_user` int(11) default NULL COMMENT 'ID of the User that generate the activity',
  `activity_object_type` varchar(64) NOT NULL COMMENT 'Object type (Yield, Discussion, etc.)',
  `activity_type` varchar(64) NOT NULL COMMENT 'Activity type (completed, edited, added, etc.)',
  `activity_result` varchar(64) default NULL,
  `activity_date` datetime NOT NULL COMMENT 'Date and time of the activity',
  PRIMARY KEY  (`ID_activity`),
  KEY `fk_activity_id_user` (`ID_user`),
  KEY `idx_ID_object_activity` (`ID_object`),
  KEY `idx_ID_user_activity` (`ID_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='System wide activity table';

-- --------------------------------------------------------

--
-- Table structure for table `attachment`
--

CREATE TABLE `attachment` (
  `ID_attachment` int(11) NOT NULL auto_increment COMMENT 'Attachment unique ID',
  `ID_user` int(11) default NULL COMMENT 'User ID that created this attachment',
  `ID_file` int(11) default NULL COMMENT 'File ID of the attachment',
  `ID_object` int(11) NOT NULL COMMENT 'ID of the object the file was attached to',
  `attachment_object_type` varchar(64) NOT NULL COMMENT 'Object type (Yield, discussion, etc)',
  PRIMARY KEY  (`ID_attachment`),
  KEY `fk_attachment_id_file` (`ID_file`),
  KEY `fk_attachment_id_user` (`ID_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Attachments table';

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `ID_company` int(11) NOT NULL auto_increment,
  `ID_user` int(11) NOT NULL,
  `company_name` varchar(64) NOT NULL,
  `company_homepage` varchar(64) default NULL,
  `company_phone` varchar(64) default NULL,
  `company_fax` varchar(64) default NULL,
  `company_address` varchar(128) default NULL,
  `company_master` enum('yes','no') NOT NULL default 'no',
  `company_is_deleted` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`ID_company`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `company_user`
--

CREATE TABLE `company_user` (
  `ID_company_user` int(11) NOT NULL auto_increment,
  `ID_company` int(11) NOT NULL,
  `ID_user` int(11) NOT NULL,
  PRIMARY KEY  (`ID_company_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE `file` (
  `ID_file` int(11) NOT NULL auto_increment COMMENT 'File unique ID',
  `ID_user` int(11) default NULL COMMENT 'ID of the user that uploaded the file',
  `file_title` varchar(128) default NULL COMMENT 'Title',
  `file_description` text COMMENT 'Description',
  `file_display` enum('yes','no') default NULL COMMENT 'Display File?',
  `file_views` int(11) default NULL COMMENT '# of Views',
  `file_is_image` enum('yes','no') default NULL COMMENT 'Is image?',
  `file_name` varchar(128) default NULL COMMENT 'File name',
  `file_type` varchar(64) default NULL COMMENT 'Type (Mime)',
  `file_path` text COMMENT 'Path',
  `file_full_path` text COMMENT 'Full path',
  `file_raw_name` varchar(250) default NULL COMMENT 'Raw name (encrypted)',
  `file_orig_name` varchar(250) default NULL COMMENT 'Original name',
  `file_extension` varchar(32) default NULL COMMENT 'File extension',
  `file_size` double default NULL COMMENT 'File Size',
  `file_image_width` int(11) default NULL COMMENT 'Image Width',
  `file_image_height` int(11) default NULL COMMENT 'Image height',
  `file_image_type` varchar(16) default NULL COMMENT 'Image Type',
  `file_uploaded_date` datetime default NULL COMMENT 'Date the file was uploaded',
  PRIMARY KEY  (`ID_file`),
  KEY `fk_file_id_user` (`ID_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Files table';

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `ID_user` int(11) NOT NULL auto_increment COMMENT 'Unique ID',
  `user_name` varchar(64) NOT NULL COMMENT 'Username',
  `user_firstname` varchar(64) default NULL COMMENT 'First Name',
  `user_middlename` varchar(64) default NULL COMMENT 'Middle Name',
  `user_surname` varchar(64) default NULL COMMENT 'Surname',
  `user_email` varchar(250) NOT NULL COMMENT 'Email address',
  `user_timezone` varchar(32) NOT NULL COMMENT 'Time Zone',
  `user_locale` varchar(18) default NULL COMMENT 'User Locale',
  `user_is_deleted` enum('yes','no') NOT NULL default 'no' COMMENT 'Is deleted',
  `user_permanently_deleted` enum('yes','no') default 'no' COMMENT 'User is permanently deleted',
  PRIMARY KEY  (`ID_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='User main table';

-- --------------------------------------------------------

--
-- Table structure for table `user_auth`
--

CREATE TABLE `user_auth` (
  `ID_auth` int(11) NOT NULL auto_increment,
  `ID_user` int(11) default NULL COMMENT 'ID of the User the Auth data belongs to',
  `user_salt` varchar(32) NOT NULL COMMENT 'Salt used to create password',
  `user_password` varchar(250) NOT NULL COMMENT 'Encrypted password',
  `user_status` int(11) NOT NULL COMMENT 'Status (0=enabled, 1=disabled)',
  `user_level` varchar(250) NOT NULL COMMENT 'Auth Level',
  `user_created` datetime default NULL COMMENT 'Created date and time',
  `user_updated` datetime default NULL COMMENT 'Update date and time',
  `user_invalid_logins` int(11) default NULL COMMENT '# of invalid logins',
  `user_banned_until` datetime default NULL COMMENT 'Banned until date and time',
  `user_secret_question` varchar(128) NOT NULL COMMENT 'Secret question',
  `user_secret_answer` varchar(128) NOT NULL COMMENT 'Secret answer',
  `user_last_login` datetime default NULL COMMENT 'Last login date and time',
  `user_avatar_file` int(11) default NULL COMMENT 'ID of the Avatar File (Image)',
  PRIMARY KEY  (`ID_auth`),
  KEY `fk_user_id_user_auth` (`ID_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Authentication data table';

-- --------------------------------------------------------

--
-- Table structure for table `user_optional`
--

CREATE TABLE `user_optional` (
  `ID_optional` int(11) NOT NULL auto_increment,
  `ID_user` int(11) NOT NULL COMMENT 'ID of the User the optional data belongs to',
  `user_address1` varchar(128) default NULL COMMENT 'Address 1',
  `user_address2` varchar(128) default NULL COMMENT 'Address 2',
  `user_city` varchar(64) default NULL COMMENT 'City',
  `user_state` varchar(32) default NULL COMMENT 'State',
  `user_zip` varchar(32) default NULL COMMENT 'ZIP Code',
  `user_position` varchar(64) default NULL COMMENT 'Position',
  `user_department` varchar(64) default NULL COMMENT 'Department',
  `user_phone` varchar(64) default NULL COMMENT 'Phone #',
  `user_phone_ext` varchar(64) default NULL,
  `user_mobile` varchar(64) default NULL COMMENT 'Mobile #',
  `user_fax` varchar(64) default NULL COMMENT 'Fax #',
  `user_aim` varchar(64) default NULL COMMENT 'AIM buddy name',
  `user_msn` varchar(64) default NULL COMMENT 'Live MSN address',
  `user_country` varchar(65) default NULL COMMENT 'Country',
  `user_region` varchar(65) default NULL COMMENT 'Region',
  PRIMARY KEY  (`ID_optional`),
  KEY `fk_user_id_user_optional` (`ID_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='User optional data table';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `privileges`
--

CREATE TABLE IF NOT EXISTS `privileges` (
  `id_privileges` int(11) NOT NULL AUTO_INCREMENT,
  `privileges_rol_data` text,
  `privileges_rol_name` varchar(150) NOT NULL,
  `privileges_is_deleted` varchar(10) NOT NULL DEFAULT 'no',
  `department` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_privileges`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `privileges`
--

INSERT INTO `privileges` (`id_privileges`, `privileges_rol_data`, `privileges_rol_name`, `privileges_is_deleted`, `department`) VALUES
(1, '{"checks_user":["manage_users","manage_groups","read_users","read_groups"],"checks_costing":["cp_list","cp_add","cp_edit","cp_archive","cp_manage","cp_print","cp_view_bid","cp_manage_bid","cp_edit_bid","cp_accept"],"checks_setup":["general_settings","roles"],"yields":["yields_manage"]}', 'Admin', 'no', 'admins');


CREATE TABLE `entidad` (
  `Id` int(11) NOT NULL auto_increment,
  `Nombre` varchar(255) default NULL,
  `URL` text default NULL,
  `Entidad` varchar(255) default NULL,
  `Tipo` varchar(255) default NULL,

  PRIMARY KEY  (`Id`),
  KEY `fk_entidad_id` (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Structure for view `vw_users_active`
--
DROP TABLE IF EXISTS `vw_users_active`;

CREATE VIEW `vw_users_active` AS select `user`.`ID_user` AS `ID_user`,`user`.`user_name` AS `user_name`,`user`.`user_firstname` AS `user_firstname`,`user`.`user_middlename` AS `user_middlename`,`user`.`user_surname` AS `user_surname`,`user`.`user_email` AS `user_email`,`user`.`user_timezone` AS `user_timezone`,`user`.`user_locale` AS `user_locale`,`user_auth`.`user_salt` AS `user_salt`,`user_auth`.`user_password` AS `user_password`,`user_auth`.`user_status` AS `user_status`,`user_auth`.`user_level` AS `user_level`,`user_auth`.`user_avatar_file` AS `user_avatar_file`,`user_optional`.`user_address1` AS `user_address1`,`user_optional`.`user_address2` AS `user_address2`,`user_optional`.`user_city` AS `user_city`,`user_optional`.`user_state` AS `user_state`,`user_optional`.`user_zip` AS `user_zip`,`user_optional`.`user_position` AS `user_position`,`user_optional`.`user_department` AS `user_department`,`user_optional`.`user_phone` AS `user_phone`,`user_optional`.`user_mobile` AS `user_mobile`,`user_optional`.`user_fax` AS `user_fax`,`user_optional`.`user_aim` AS `user_aim`,`user_optional`.`user_msn` AS `user_msn`,`user_optional`.`user_country` AS `user_country`,`user_optional`.`user_region` AS `user_region` from ((`user` left join `user_auth` on((`user`.`ID_user` = `user_auth`.`ID_user`))) left join `user_optional` on((`user`.`ID_user` = `user_optional`.`ID_user`))) where ((`user`.`user_is_deleted` = _utf8'no') and (`user`.`user_permanently_deleted` = _utf8'no')) order by `user`.`ID_user`;


