/**
 * Author:   Ernest Wojciuk
 * Web Site: www.imap.pl
 * Email:    ernest@moldo.pl
 * Comments: EMAIL TO DB
 */


CREATE TABLE `emailtodb_email` (
  `ID` int(11) NOT NULL auto_increment,
  `IDEmail` varchar(255) NOT NULL default '0',
  `EmailFrom` varchar(255) NOT NULL default '',
  `EmailFromP` varchar(255) NOT NULL default '',
  `EmailTo` varchar(255) NOT NULL default '',
  `DateE` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateDb` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateRead` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateRe` datetime NOT NULL default '0000-00-00 00:00:00',
  `Status` tinyint(3) NOT NULL default '0',
  `Type` tinyint(3) NOT NULL default '0',
  `Del` tinyint(3) NOT NULL default '0',
  `Subject` varchar(255) default NULL,
  `Message` text  NOT NULL,
  `Message_html` text  NOT NULL,
  `MsgSize` int(11) NOT NULL default '0',
  `Kind` tinyint(2) NOT NULL default '0',
  `IDre` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `IDEmail` (`IDEmail`),
  KEY `EmailFrom` (`EmailFrom`)
) ENGINE=MyISAM;


CREATE TABLE `emailtodb_dir` (
  `IDdir` int(11) NOT NULL auto_increment,
  `IDsubdir` int(11) NOT NULL default '0',
  `Sort` int(11) NOT NULL default '0',
  `Name` varchar(25) NOT NULL default '',
  `Status` tinyint(3) NOT NULL default '0',
  `CatchMail` varchar(150) NOT NULL default '',
  `Icon` varchar(250)  NOT NULL default '',
  PRIMARY KEY  (`IDdir`),
  KEY `IDsubdir` (`IDsubdir`)
) ENGINE=MyISAM;


CREATE TABLE `emailtodb_list` (
  `IDlist` int(11) NOT NULL auto_increment,
  `Email` varchar(255) NOT NULL default '',
  `Type` char(2) NOT NULL default 'B',
  PRIMARY KEY  (`IDlist`),
  KEY `Email` (`Email`)
) ENGINE=MyISAM;


CREATE TABLE `emailtodb_log` (
  `IDlog` int(11) NOT NULL auto_increment,
  `IDemail` int(11) NOT NULL default '0',
  `Email` varchar(150) NOT NULL default '',
  `Info` varchar(255)  NOT NULL default '',
  `FSize` int(11) NOT NULL default '0',
  `Date_start` datetime NOT NULL default '0000-00-00 00:00:00',
  `Date_finish` datetime NOT NULL default '0000-00-00 00:00:00',
  `Status` int(3) NOT NULL default '0',
  `Dif` int(11) NOT NULL default '0',
  PRIMARY KEY  (`IDlog`)
) ENGINE=MyISAM;


CREATE TABLE `emailtodb_words` (
  `IDw` int(11) NOT NULL auto_increment,
  `Word` varchar(100)  NOT NULL default '',
  PRIMARY KEY  (`IDw`),
  KEY `Word` (`Word`)
) ENGINE=MyISAM;


CREATE TABLE `emailtodb_attach` (
  `ID` int(11) NOT NULL auto_increment,
  `IDEmail` int(11) NOT NULL default '0',
  `FileNameOrg` varchar(255) NOT NULL default '',
  `Filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `IDEmail` (`IDEmail`)
) ENGINE=MyISAM;
    