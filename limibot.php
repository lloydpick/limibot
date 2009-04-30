<?php

	// Get the currect unix time so we can calculate uptime
	$time1 = time();
	
	// Allow script to continue to infinity
	set_time_limit(0);
	
	// Include the custom functions and settings
	include ("irc_functions.php");
	
	// mySQL Database Settings
        $IRC_mySQL_Info_Server = "localhost";
        $IRC_mySQL_Info_User = "limibot";
        $IRC_mySQL_Info_Pass = "botofwin";
        $IRC_mySQL_Info_DataB = "limibot";

	// Connect to the mySQL Database
	IRC_mySQL_Connect("$IRC_mySQL_Info_Server","$IRC_mySQL_Info_User","$IRC_mySQL_Info_Pass","$IRC_mySQL_Info_DataB");
	
	// IRC Server Settings Retrieved from database
	$IRC_Server_IP = IRC_Setting(ServerIP);
	$IRC_Server_Port = IRC_Setting(ServerPort);
	$IRC_Server_Pass = IRC_Setting(ServerPass);
	$IRC_Server_Nick = IRC_Setting(ServerNick);
	
	// Print Various Bot Info
	IRC_Info("$IRC_Server_IP","$IRC_Server_Port","$IRC_Server_Pass","$IRC_Server_Nick");
	
	// Start the Bot
	IRC_Start("$IRC_Server_IP","$IRC_Server_Port","$IRC_Server_Pass","$IRC_Server_Nick","$IRC_Admin_Nick","$IRC_Admin_Pass","$IRC_Admin_Host");
	
?>
