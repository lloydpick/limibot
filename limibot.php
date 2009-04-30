<?php

	function StartBot () {
	
		// Allow script to continue to infinity
		set_time_limit(0);
		
		// Include the custom functions and settings
		include_once("irc_functions.php");
	
		// mySQL Database Settings
		$IRC_mySQL_Info_Server = "";
		$IRC_mySQL_Info_User = "";
		$IRC_mySQL_Info_Pass = "";
		$IRC_mySQL_Info_DataB = "";
	
		// Connect to the mySQL Database
		IRC_mySQL_Connect("$IRC_mySQL_Info_Server","$IRC_mySQL_Info_User","$IRC_mySQL_Info_Pass","$IRC_mySQL_Info_DataB");
		 
		// Unset the variables for security
		unset($IRC_mySQL_Info_Server);
		unset($IRC_mySQL_Info_User);
		unset($IRC_mySQL_Info_Pass);
		unset($IRC_mySQL_Info_DataB);
	
		// IRC Server Settings Retrieved from database
		$IRC_Server_IP = IRC_Setting(ServerIP);
		$IRC_Server_Port = IRC_Setting(ServerPort);
		$IRC_Server_Pass = IRC_Setting(ServerPass);
		$IRC_Server_Nick = IRC_Setting(ServerNick);
	
		// Print Various Bot Info
		IRC_Info("$IRC_Server_IP","$IRC_Server_Port","$IRC_Server_Pass","$IRC_Server_Nick");
	
		// Start the Bot
		IRC_Start("$IRC_Server_IP","$IRC_Server_Port","$IRC_Server_Pass","$IRC_Server_Nick");
	
	}
	
	// Following code is used to restart the bot if requested to
	global $restart;
	$restart = 1;
	
	// Restart loop
	while ($restart == 1) {
	
		// So we jump out the while loop and start again
		$restart = 0;
		
		// Start the bot
		StartBot();
		
	}

?>