CREATE TABLE `admins` (
  `id` bigint(20) NOT NULL auto_increment,
  `nick` tinytext NOT NULL,
  `pass` tinytext NOT NULL,
  `host` tinytext NOT NULL,
  `email` mediumtext NOT NULL,
  `access` mediumtext NOT NULL,
  `super` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 COMMENT='The Lists of Admin Nicknames, Passwords and thier Hosts' AUTO_INCREMENT=2;

INSERT INTO `admins` VALUES (1, 'Example', 'password', 'some.host.co.uk', 'limibot@some.host.co.uk', ' 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 ', 1);


CREATE TABLE `admins_commands` (
  `id` bigint(20) NOT NULL auto_increment,
  `command` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `usage` mediumtext NOT NULL,
  `example` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Admin Command Table, contains all Admin Commands' AUTO_INCREMENT=19;

INSERT INTO `admins_commands` VALUES (1, 'HELP', 'Lists all the Admin Commands Available to the specific administrator.', 'HELP [COMMAND]', 'HELP ADMIN');
INSERT INTO `admins_commands` VALUES (2, 'ADMIN ADD', 'Add an admin to the bot', 'ADMIN ADD [NICKNAME] [PASSWORD] [IP/HOST]', 'ADMIN ADD Limited m00 adsl.multiplay.co.uk');
INSERT INTO `admins_commands` VALUES (3, 'ADMIN DEL', 'Delete an admin from the Bot', 'ADMIN DEL [NICKNAME]', 'ADMIN DEL Wizzo');
INSERT INTO `admins_commands` VALUES (4, 'ADMIN LIST', 'Lists all the admins and superadmins for the Bot', 'ADMIN LIST', 'ADMIN LIST');
INSERT INTO `admins_commands` VALUES (5, 'CHANNEL ADD', 'Get the Bot to join a channel and create statistics for it.', 'CHANNEL ADD [#CHANNEL]', 'CHANNEL ADD #multiplay');
INSERT INTO `admins_commands` VALUES (6, 'CHANNEL DEL', 'Delete a channel from the bot, the bot will no longer join this channel', 'CHANNEL DEL [#CHANNEL]', 'CHANNEL DEL #multiplay');
INSERT INTO `admins_commands` VALUES (7, 'CHANNEL LIST', 'Will list all the channels the bot is currently on and producing stats for.', 'CHANNEL LIST', 'CHANNEL LIST');
INSERT INTO `admins_commands` VALUES (8, 'MODE OP', 'Will Op someone on the channel specified. The bot must be op\'d in the first place for this to work. This command is not permanent.', 'MODE OP [#CHANNEL] [NICKNAME]', 'MODE OP #multiplay Wizzo');
INSERT INTO `admins_commands` VALUES (9, 'MODE DEOP', 'Will De-Op someone on the channel specified. The bot must be op\'d in the first place for this to work. This command is not permanent.', 'MODE DEOP [#CHANNEL] [NICKNAME]', 'MODE DEOP #multiplay Wizzo');
INSERT INTO `admins_commands` VALUES (10, 'MODE VOICE', 'Will Voice someone on the channel specified. The bot must be op\'d in the first place for this to work. This command is not permanent.', 'MODE VOICE [#CHANNEL] [NICKNAME]', 'MODE VOICE #multiplay Wizzo');
INSERT INTO `admins_commands` VALUES (11, 'MODE DEVOICE', 'Will De-Voice someone on the channel specified. The bot must be op\'d in the first place for this to work. This command is not permanent.', 'MODE DEVOICE [#CHANNEL] [NICKNAME]', 'MODE DEVOICE #multiplay Wizzo');
INSERT INTO `admins_commands` VALUES (12, 'MODE KICK', 'Will kick the specified nickname out of the channel. The bot must be op\'d in the first place for this to work.', 'MODE KICK [#CHANNEL] [NICKNAME]', 'MODE KICK #multiplay Wizzo');
INSERT INTO `admins_commands` VALUES (13, 'SET TOPIC', 'Will set the topic of the specifed channel to whatever you type in. Bot has to be op\'d in the channel.', 'SET TOPIC [#CHANNEL] [TOPIC]', 'SET TOPIC #multiplay Welcome to MultiplayUK - The Biggest LAN Events in the UK!');
INSERT INTO `admins_commands` VALUES (14, 'RESTART', 'Will cause the bot to exit and restart. Warning, if you hav\'nt set a cron job or use a bat file to automatically re-load the bot it will exit and not come back up.', 'RESTART', 'RESTART');
INSERT INTO `admins_commands` VALUES (15, 'ADMIN CHANGE PASSWORD', 'Allows you to change your password to access the bot.', 'ADMIN CHANGE PASSWORD [OLDPASSWORD] [NEWPASSWORD]', 'ADMIN CHANGE PASSWORD f00 m00');
INSERT INTO `admins_commands` VALUES (16, 'ADMIN CHANGE HOST', 'Allows you to change your host which you use to access the bot.', 'ADMIN CHANGE HOST [OLDHOST] [NEWHOST]', 'ADMIN CHANGE HOST adsl.mailbox.net.uk adsl.multiplay.co.uk');
INSERT INTO `admins_commands` VALUES (17, 'ADMIN CHANGE EMAIL', 'Allows you to change your e-mail address which the bot knows.', 'ADMIN CHANGE EMAIL [NEWEMAIL]', 'ADMIN CHANGE EMAIL limited@multiplay.co.uk');
INSERT INTO `admins_commands` VALUES (18, 'IDENTIFY', 'Makes the Bot identify to services which you have specified in the bots settings. Mainly for use after services have crashed and have come back online.', 'IDENTIFY', 'IDENTIFY');


CREATE TABLE `admins_temp` (
  `id` bigint(20) NOT NULL auto_increment,
  `admin` tinytext NOT NULL,
  `account` tinytext NOT NULL,
  `time` timestamp(14) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Admin Temporary Table for Storing Logged In Admins' AUTO_INCREMENT=1;


CREATE TABLE `channels` (
  `id` tinyint(4) NOT NULL auto_increment,
  `channel` varchar(30) NOT NULL default '',
  `topic_message` tinytext NOT NULL,
  `topic_set` tinytext NOT NULL,
  `topic_author` tinytext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `channel` (`channel`)
) TYPE=MyISAM PACK_KEYS=0 COMMENT='The List of Channels the Bot is currently In' AUTO_INCREMENT=2;

INSERT INTO `channels` VALUES (1, '#LimiNET', '', '', '');


CREATE TABLE `commands` (
  `id` bigint(20) NOT NULL auto_increment,
  `active` tinyint(4) NOT NULL default '1',
  `command` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `usage` mediumtext NOT NULL,
  `filename` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=5;

INSERT INTO `commands` VALUES (1, 1, '!stats', 'Retrieve individual stats for the nickname given and for the active channel', '!stats <nickname>', 'addon_userstats.php');
INSERT INTO `commands` VALUES (2, 1, '!system', 'Grabs the output of the unix command \'uptime\' and sends it to the channel', '!system', 'addon_system.php');
INSERT INTO `commands` VALUES (3, 1, '!uptime', 'Shows the Bots Uptime', '!uptime', 'addon_botuptime.php');
INSERT INTO `commands` VALUES (4, 1, '!seen', 'Returns Seen Information for the supplied nickname', '!seen <nickname>', 'addon_seen.php');


CREATE TABLE `commands_joins` (
  `id` bigint(20) NOT NULL auto_increment,
  `active` tinyint(4) NOT NULL default '1',
  `name` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `filename` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Contains the information on what files to run on nick join' AUTO_INCREMENT=2 ;

INSERT INTO `commands_joins` VALUES (1, 0, 'Test Thingy', 'It\'s well.. a test thingy.. who has a fucking clue what it does.. I know I don\'t', 'addon_jointest.php');


CREATE TABLE `logs` (
  `id` bigint(20) NOT NULL auto_increment,
  `time` timestamp(14) NOT NULL,
  `nick` bigint(20) NOT NULL default '0',
  `channel` bigint(20) NOT NULL default '0',
  `type` tinytext NOT NULL,
  `log` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='The Main IRC Chat Log for all Channels the Bot is in' AUTO_INCREMENT=1;


CREATE TABLE `logs_admin` (
  `id` bigint(20) NOT NULL auto_increment,
  `time` timestamp(14) NOT NULL,
  `acc` bigint(20) NOT NULL default '0',
  `nick` bigint(20) NOT NULL default '0',
  `log` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='The Log of Admin Commands Used, By Who and When' AUTO_INCREMENT=1;


CREATE TABLE `nicks` (
  `id` bigint(20) NOT NULL auto_increment,
  `nick` varchar(30) NOT NULL default '',
  `first` timestamp(14) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `nicks` (`nick`)
) TYPE=MyISAM PACK_KEYS=0 COMMENT='The Lists of the NickNames the Bot has "Seen"' AUTO_INCREMENT=1;


CREATE TABLE `settings` (
  `setting` tinytext NOT NULL,
  `value` tinytext NOT NULL,
  `comment` mediumtext NOT NULL
) TYPE=MyISAM COMMENT='Settings such as IRC Server Details';

INSERT INTO `settings` VALUES ('ServerIP', '', 'IRC Server IP');
INSERT INTO `settings` VALUES ('ServerPort', '', 'IRC Server Port');
INSERT INTO `settings` VALUES ('ServerPass', '', 'IRC Server Password');
INSERT INTO `settings` VALUES ('ServerNick', '', 'Bot Nickname');
INSERT INTO `settings` VALUES ('NickIdentify', '', 'NickServ Login Command');
INSERT INTO `settings` VALUES ('BotStart', '', 'The time the bot connected to IRC (Automatically Entered)');
INSERT INTO `settings` VALUES ('ShowRaw', '0', 'Show Raw IRC Commands in the Console');
INSERT INTO `settings` VALUES ('PID', '', 'Process ID Number for the Bot');
INSERT INTO `settings` VALUES ('Version', '0.6.4 Beta (Public Release)', 'LimiBot Version Number');

CREATE TABLE `statistics` (
  `id` bigint(20) NOT NULL auto_increment,
  `nick` bigint(20) NOT NULL default '0',
  `channel` bigint(20) NOT NULL default '0',
  `lastseen` timestamp(14) NOT NULL,
  `host` mediumtext NOT NULL,
  `joins` bigint(20) NOT NULL default '0',
  `typing` float NOT NULL default '0',
  `characters` bigint(20) NOT NULL default '0',
  `words` bigint(20) NOT NULL default '0',
  `lines` bigint(20) NOT NULL default '0',
  `bans` bigint(20) NOT NULL default '0',
  `banned` bigint(20) NOT NULL default '0',
  `unbans` bigint(20) NOT NULL default '0',
  `unbanned` bigint(20) NOT NULL default '0',
  `kicks` bigint(20) NOT NULL default '0',
  `kicked` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 COMMENT='Highly Detailed Information about Different Nicknames' AUTO_INCREMENT=1;