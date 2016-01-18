#
# Table structure for table `mypoints_userpoints`
#

CREATE TABLE mypoints_user (
  useruid int(10) NOT NULL default '0',
  useruname varchar(50) NOT NULL default '',
  userpoints int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (useruid),
  KEY uuname (useruname)
) ENGINE=MyISAM;

#
# Table structure for table `mypoints_plugins`
#

CREATE TABLE mypoints_plugin (
  pluginid int(10) unsigned NOT NULL auto_increment,
  pluginmid smallint(4) unsigned NOT NULL default '0',
  pluginname varchar(50) NOT NULL default '',
  plugintype enum ('items','votes') NOT NULL default 'items',
  pluginmulti int(10) unsigned NOT NULL default '1',
  pluginisactive int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (pluginid),
  KEY pmid (pluginmid)
) ENGINE=MyISAM;

#
# Table structure for table `mypoints_plugins`
#

CREATE TABLE mypoints_relation (
  relationid int(10) unsigned NOT NULL auto_increment,
  relationuid int(10) unsigned NOT NULL default '0',
  relationpid int(10) unsigned NOT NULL default '0',
  relationpoints int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (relationid),
  KEY ruid (relationuid)
) ENGINE=MyISAM;
