<?php

	// This file is for upgrading your version of limibot to the latest version you have downloaded
	// At present this will upgrade from version 0.6.2, 0.6.3, 0.6.4 to 0.6.5
	// 
	// Operation:
	//
	// This has been designed to work from the console, it may however work from a webpage, but I don't
	// recommend it at all. To actually upgrade your databases to the latest version you need to input
	// your mySQL database username and password.
	
	// mySQL Database Host
	$mysql[host] = "";
	// mySQL Database Username
	$mysql[user] = "";
	// mySQL Database Password
	$mysql[pass] = "";
	// mySQL Database Name
	$mysql[data] = "";
	
	// Do not edit below this line
	
	echo "==========================================================\n";
	echo "LimiBot SQL Database Upgrader 0.6.2, 0.6.3, 0.6.4 -> 0.6.5\n";
	echo "==========================================================\n\n";
	
	if ($mysql[host] == NULL OR $mysql[user] == NULL OR $mysql[data] == NULL) {
		die("ERROR: Missing mySQL Host, User or Database, please edit this file!\n");
	} 
	
	echo "Logging into mySQL Database...";
	$mysql[connect] = mysql_connect("$mysql[host]", "$mysql[user]", "$mysql[pass]");
	if (!$mysql[connect]) { die('\n\nERROR: ' . mysql_error()); }
	echo "  Done\n";
	echo "Selecting Database...";
	$mysql[database] = mysql_select_db("$mysql[data]",$mysql[connect]);
    if (!$mysql[database]) { die('\n\nERROR: ' . mysql_error()); }
	echo "  Done\n";
	echo "Checking LimiBot Version...";
	$mysql[query] = "SELECT * FROM `settings` WHERE `setting` = 'Version'";
	$mysql[result] = mysql_query($mysql[query]);
	$limibot[version] = @mysql_result($mysql[result],0,"value");
	$limibot[version] = explode(" ",$limibot[version]);
	$limibot[version] = $limibot[version][0];
	if ($limibot[version] < "0.6.3") {
		echo "  Done\n";
		echo "\nBeginning Upgrade from 0.6.2...\n";
		echo "Patch 1 -> Creating mySQL Table `temp`...";
		$mysql[query] = 'CREATE TABLE `temp` (`id` mediumint(9) NOT NULL AUTO_INCREMENT,';
		$mysql[query] .= '`option` mediumtext NOT NULL,';
		$mysql[query] .= '`value` mediumtext NOT NULL,';
		$mysql[query] .= 'PRIMARY KEY (`id`)) TYPE = MYISAM COMMENT = \'Used for storing temporary values\'';
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 2 -> Updating Addons Table...";
		$mysql[query] = "INSERT INTO `commands` VALUES ('', 1, 'NOTRIGGER', 'Admin Commands', 'NOTRIGGER', 'addon_admin.php');";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 3 -> Updating Addons Table Structure...";
		$mysql[query] = "ALTER TABLE `commands` ADD `webmodule` TINYTEXT NOT NULL;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 4 -> Updating Join Addons Table Structure...";
		$mysql[query] = "ALTER TABLE `commands_joins` ADD `webmodule` TINYTEXT NOT NULL;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 5 -> Updating Channels Table Structure...";
		$mysql[query] = "ALTER TABLE `channels` ADD `key` TINYTEXT NOT NULL AFTER `channel`;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 6 -> Updating Admins Access String...";
		$mysql[query] = "SELECT * FROM `admins`";
		$mysql[result] = mysql_query($mysql[query]);
		$mysql[total] = mysql_num_rows($mysql[result]);
		$mysql[temp] = 0;
		while ($mysql[total] > $mysql[temp]) {
			$Admin_ID = mysql_result($mysql[result],$mysql[temp],"id");
			$Admin_Nick = mysql_result($mysql[result],$mysql[temp],"nick");
			$Admin_Pass = mysql_result($mysql[result],$mysql[temp],"pass");
			$Admin_Host = mysql_result($mysql[result],$mysql[temp],"host");
			$Admin_Email = mysql_result($mysql[result],$mysql[temp],"email");
			$Admin_Access = mysql_result($mysql[result],$mysql[temp],"access");
			$Admin_Super = mysql_result($mysql[result],$mysql[temp],"super");
			$Admin_Access_New = ereg_replace(' ', '|', $Admin_Access);
			echo " - $Admin_Nick Updated\n";
			$mysql[query2] = "UPDATE `admins` SET `access` = '$Admin_Access_New' WHERE `id` = '$Admin_ID'";
			$mysql[result2] = mysql_query($mysql[query2]);
			$mysql[temp]++;
		}
		echo "  Done\n";
		echo "Patch 7 -> Updating Addons Table...";
		$mysql[query] = "INSERT INTO `settings` VALUES ('ServerNickSecondary', 'LimiBot-', 'Switch to this nickname if our primary nickname is in use')";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 8 -> Updating Version Number...";
		$mysql[query] = "INSERT INTO `settings` VALUES ('Version', '0.6.5 Beta (Public Release)', 'LimiBot Version Number')";
		$mysql[result] = mysql_query($mysql[query]);
		echo "SQL Upgrade to 0.6.5 Successfully Completed!\n";
	}
	if ($limibot[version] == "0.6.3") {
		echo "  Done\n";
		echo "\nBeginning Upgrade from 0.6.3...\n";
		echo "Patch 1 -> Creating mySQL Table `temp`...";
		$mysql[query] = 'CREATE TABLE `temp` (`id` mediumint(9) NOT NULL AUTO_INCREMENT,';
		$mysql[query] .= '`option` mediumtext NOT NULL,';
		$mysql[query] .= '`value` mediumtext NOT NULL,';
		$mysql[query] .= 'PRIMARY KEY (`id`)) TYPE = MYISAM COMMENT = \'Used for storing temporary values\'';
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 2 -> Updating Addons Table...";
		$mysql[query] = "INSERT INTO `commands` VALUES ('', 1, 'NOTRIGGER', 'Admin Commands', 'NOTRIGGER', 'addon_admin.php');";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 3 -> Updating Addons Table Structure...";
		$mysql[query] = "ALTER TABLE `commands` ADD `webmodule` TINYTEXT NOT NULL;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 4 -> Updating Joins Addons Table Structure...";
		$mysql[query] = "ALTER TABLE `commands_joins` ADD `webmodule` TINYTEXT NOT NULL;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 5 -> Updating Channels Table Structure...";
		$mysql[query] = "ALTER TABLE `channels` ADD `key` TINYTEXT NOT NULL AFTER `channel`;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 6 -> Updating Admins Access String...";
		$mysql[query] = "SELECT * FROM `admins`";
		$mysql[result] = mysql_query($mysql[query]);
		$mysql[total] = mysql_num_rows($mysql[result]);
		$mysql[temp] = 0;
		while ($mysql[total] > $mysql[temp]) {
			$Admin_ID = mysql_result($mysql[result],$mysql[temp],"id");
			$Admin_Nick = mysql_result($mysql[result],$mysql[temp],"nick");
			$Admin_Pass = mysql_result($mysql[result],$mysql[temp],"pass");
			$Admin_Host = mysql_result($mysql[result],$mysql[temp],"host");
			$Admin_Email = mysql_result($mysql[result],$mysql[temp],"email");
			$Admin_Access = mysql_result($mysql[result],$mysql[temp],"access");
			$Admin_Super = mysql_result($mysql[result],$mysql[temp],"super");
			$Admin_Access_New = ereg_replace(' ', '|', $Admin_Access);
			$mysql[query2] = "UPDATE `admins` SET `access` = '$Admin_Access_New' WHERE `id` = '$Admin_ID'";
			$mysql[result2] = mysql_query($mysql[query2]);
			$mysql[temp]++;
		}
		echo "  Done\n";
		echo "Patch 7 -> Updating Addons Table...";
		$mysql[query] = "INSERT INTO `settings` VALUES ('ServerNickSecondary', 'LimiBot-', 'Switch to this nickname if our primary nickname is in use')";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 8 -> Updating Version Number...";
		$mysql[query] = "UPDATE `settings` SET `value` = '0.6.5 Beta (Public Release)' WHERE `setting` = 'Version'";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "SQL Upgrade to 0.6.5 Successfully Completed!\n";
	}
	if ($limibot[version] == "0.6.4") {
		echo "  Done\n";
		echo "\nBeginning Upgrade from 0.6.4...\n";
		echo "Patch 1 -> Creating mySQL Table `temp`...";
		$mysql[query] = 'CREATE TABLE `temp` (`id` mediumint(9) NOT NULL AUTO_INCREMENT,';
		$mysql[query] .= '`option` mediumtext NOT NULL,';
		$mysql[query] .= '`value` mediumtext NOT NULL,';
		$mysql[query] .= 'PRIMARY KEY (`id`)) TYPE = MYISAM COMMENT = \'Used for storing temporary values\'';
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 2 -> Updating Addons Table...";
		$mysql[query] = "INSERT INTO `commands` VALUES ('', 1, 'NOTRIGGER', 'Admin Commands', 'NOTRIGGER', 'addon_admin.php');";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 3 -> Updating Addons Table Structure...";
		$mysql[query] = "ALTER TABLE `commands` ADD `webmodule` TINYTEXT NOT NULL;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 4 -> Updating Joins Addons Table Structure...";
		$mysql[query] = "ALTER TABLE `commands_joins` ADD `webmodule` TINYTEXT NOT NULL;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 5 -> Updating Channels Table Structure...";
		$mysql[query] = "ALTER TABLE `channels` ADD `key` TINYTEXT NOT NULL AFTER `channel`;";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 6 -> Updating Admins Access String...";
		$mysql[query] = "SELECT * FROM `admins`";
		$mysql[result] = mysql_query($mysql[query]);
		$mysql[total] = mysql_num_rows($mysql[result]);
		$mysql[temp] = 0;
		while ($mysql[total] > $mysql[temp]) {
			$Admin_ID = mysql_result($mysql[result],$mysql[temp],"id");
			$Admin_Nick = mysql_result($mysql[result],$mysql[temp],"nick");
			$Admin_Pass = mysql_result($mysql[result],$mysql[temp],"pass");
			$Admin_Host = mysql_result($mysql[result],$mysql[temp],"host");
			$Admin_Email = mysql_result($mysql[result],$mysql[temp],"email");
			$Admin_Access = mysql_result($mysql[result],$mysql[temp],"access");
			$Admin_Super = mysql_result($mysql[result],$mysql[temp],"super");
			$Admin_Access_New = ereg_replace(' ', '|', $Admin_Access);
			$mysql[query2] = "UPDATE `admins` SET `access` = '$Admin_Access_New' WHERE `id` = '$Admin_ID'";
			$mysql[result2] = mysql_query($mysql[query2]);
			$mysql[temp]++;
		}
		echo "  Done\n";
		echo "Patch 7 -> Updating Addons Table...";
		$mysql[query] = "INSERT INTO `settings` VALUES ('ServerNickSecondary', 'LimiBot-', 'Switch to this nickname if our primary nickname is in use')";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "Patch 8 -> Updating Version Number...";
		$mysql[query] = "UPDATE `settings` SET `value` = '0.6.5 Beta (Public Release)' WHERE `setting` = 'Version'";
		$mysql[result] = mysql_query($mysql[query]);
		echo "  Done\n";
		echo "SQL Upgrade to 0.6.5 Successfully Completed!\n";
	}
	if ($limibot[version] >= "0.6.5") {
		echo "  Done\n";
		die("\nYour LimiBot ($limibot[version]) is too new for this upgrader!\n");
	}

?>