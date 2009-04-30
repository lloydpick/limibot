<?php
	
	// Split the string up
	$command = explode(" ",$message);
	$command_ = $command;
	$command_ = explode(" ",$message);
	
	// Extract User and Message Data
	// $IRC_Command_User = :sna!sniper@211.199.132.97
	$IRC_Command_User = $command[0];
	
	// $IRC_Command_Type = PRIVMSG
	$IRC_Command_Type = $command[1];
	unset ($command[1]);
	unset ($command[2]);
	$IRC_Command_Msg = implode(" ",$command);
	
	// Extract specific PrivMsg data
	// $IRC_Command_User = alphaBot!bot@211.199.132.97
	$IRC_Command_User = substr($IRC_Command_User,1,strlen($IRC_Command_User)); 
	$IRC_Msg_Host_Temp = explode('@',$IRC_Command_User);
	$IRC_Msg_Host = $IRC_Msg_Host_Temp[1];
	 
	// $IRC_Msg_Nick = LimiBot
	$IRC_Msg_User_Temp = explode('!',$IRC_Command_User);
	$IRC_Msg_Nick = $IRC_Msg_User_Temp[0];
	
	// $IRC_Msg_Target = #multiplay
	$IRC_Msg_Temp = explode(" ",$IRC_Command_Msg);
	$IRC_Msg_Target = $command_[2];
	unset ($IRC_Msg_Temp[0]);
	
	// $IRC_Msg = Hello
	$IRC_Msg_Msg_Temp = implode(" ",$IRC_Msg_Temp);
	unset ($IRC_Msg_Temp);
	$IRC_Msg = substr($IRC_Msg_Msg_Temp,1,strlen($IRC_Msg_Msg_Temp)-1);
	unset ($IRC_Msg_Msg_Temp);
	
	// Do this if the string is longer than 3 words
	if (sizeof($command_) > 3) {
	
		// The highest number of commands come through here
		if ($IRC_Command_Type == 'PRIVMSG') {
		
			// Get the specific id for the channel from the stats database
			$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Msg_Target");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Msg_Nick","$IRC_Channel_ID");
			
			// Update a host for the specific nickname			
			IRC_Host("$IRC_Nick_ID","$IRC_Channel_ID","$IRC_Msg_Host");
		
			// Explode the message apart to see if its an ACTION or normal message
			$IRC_CTCP_Check = substr($IRC_Msg, 0, 1);
			
			// All CTCP commands start with \001 so if this is true, its CTCP
			if ($IRC_CTCP_Check == "\001") {
			
				// Take the ctcp control code off the front
				$IRC_CTCP = substr($IRC_Msg, 1);
				$IRC_CTCP = substr($IRC_CTCP, 0, -1);
				$IRC_CTCP = explode(" ",$IRC_CTCP);
				
				if ($IRC_CTCP[0] == "ACTION") {
				
					// Lets not play around with the main $IRC_Msg, as we might break something if we remove the ACTION from it
					// Plus if we ever want to look at the log, we wont have a clue what ones are ACTIONS and which ones arnt
					// ...and im bored
					$IRC_Msg_Action = $IRC_Msg;
					$IRC_Msg_Action = substr($IRC_Msg_Action, 8);
					
					// Send it to the console command for printing
					IRC_Console("Msg","[C:$IRC_Channel_ID|N:$IRC_Nick_ID] * $IRC_Msg_Nick $IRC_Msg_Action");
				
				// Send a reply to the FINGER request
				} else if ($IRC_CTCP[0] == "FINGER") {
				
					$IRC_LimiBot_Nickname = IRC_Setting("ServerNick");
					$IRC_LimiBot_Version = IRC_Setting("Version");
					IRC_Send("NOTICE $IRC_Msg_Nick :\001FINGER LimiBot - Nick: $IRC_LimiBot_Nickname\001\n\n");
					IRC_Console("Ctp","CTCP Request for $IRC_CTCP[0] from $IRC_Msg_Nick");
				
				// Send a reply to the PING request
				} else if ($IRC_CTCP[0] == "PING") {
				
					IRC_Send("NOTICE $IRC_Msg_Nick :\001PING $IRC_CTCP[1]\001\n\n");
					IRC_Console("Ctp","CTCP Request for $IRC_CTCP[0] from $IRC_Msg_Nick");
				
				// Send a reply to the TIME request
				} else if ($IRC_CTCP[0] == "TIME") {
				
					$IRC_Time = date("D M d H:i:s Y T");
					IRC_Send("NOTICE $IRC_Msg_Nick :\001TIME $IRC_Time\001\n\n");				
					IRC_Console("Ctp","CTCP Request for $IRC_CTCP[0] from $IRC_Msg_Nick");
				
				// Send a reply to the VERSION request
				} else if ($IRC_CTCP[0] == "VERSION") {
				
					$IRC_LimiBot_Version = IRC_Setting("Version");
					IRC_Send("PRIVMSG $IRC_Msg_Nick :\001VERSION LimiBot - $IRC_LimiBot_Version - https://sourceforge.net/projects/limibot/\001\n\n");				
					IRC_Console("Ctp","CTCP Request for $IRC_CTCP[0] from $IRC_Msg_Nick");
					
				}
			
			// If its a normal message...
			} else {
			
				// Send it to the console command for printing
				IRC_Console("Msg","[C:$IRC_Channel_ID|N:$IRC_Nick_ID] <$IRC_Msg_Nick> $IRC_Msg");
			
			}
			
			// Add it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Msg","$IRC_Msg");
			
			// Start the IRC Stats Parser
			IRC_Stats("$IRC_Channel_ID","$IRC_Nick_ID","$IRC_Msg");
			
			// Starts the addon program
			IRC_Addon("$IRC_Nick_ID","$IRC_Channel_ID","$IRC_Msg_Host","$IRC_Msg");
						
		}
		
		// This code will be run everytime a mode is changed.. oh the care, i can see it all over your face!
		if ($IRC_Command_Type == 'MODE') {
		
			$IRC_Mode_Channel = $command_[2];
			$IRC_Mode_Channel_ID = IRC_Channel_Lookup($IRC_Mode_Channel);
			$IRC_Mode_Command = $command_[3];
			$IRC_Mode_User = $command_[4];
			
			// This will be true if its a channel mode change, not that someone set a mode on no one ;P
			if ($IRC_Mode_User == NULL) {
			
				IRC_Console("Mde","[C:$IRC_Mode_Channel_ID] $IRC_Msg_Nick sets mode: $IRC_Mode_Command");
			
			} else {
			
				IRC_Console("Mde","[C:$IRC_Mode_Channel_ID] $IRC_Msg_Nick sets mode: $IRC_Mode_Command $IRC_Mode_User");
			
			}
		
		}
		
		// This code will be run everytime a topic is shown or a topic is changed
		if ($IRC_Command_Type == 'TOPIC') {
		
			// Take off the two new lines
			$IRC_Topic = $IRC_Msg;
			
			// Get Current Time
			$IRC_Topic_Set = time();
			
			// Update the database with the message
			$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_message` = '$IRC_Topic' WHERE `id` = '$IRC_Channel_ID'";
			$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
			
			// Update the database with the time the topic was set
			$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_set` = '$IRC_Topic_Set' WHERE `id` = '$IRC_Channel_ID'";
			$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
			
			$IRC_Nick = IRC_Nick_Lookup_ID("$IRC_Nick_ID");
			
			// Update the database with the author of this cruel creation of a topic
			$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_author` = '$IRC_Nick' WHERE `id` = '$IRC_Channel_ID'";
			$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
			
			// Add it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Topic","$IRC_Topic");
			
			// Show it in the console
			IRC_Console("Tpc","[C:$IRC_Channel_ID|N:$IRC_Nick_ID] $IRC_Topic");
			
			// Show it in the console.. again.. some people eh?
			IRC_Console("Tpc","[C:$IRC_Channel_ID] Topic Author: $IRC_Nick");
			
			// And the date...
			IRC_Console("Tpc","[C:$IRC_Channel_ID] Topic Date: $IRC_Topic_Set");
		
		}
		
		// This code will be run everytime someone quits the network
		if ($IRC_Command_Type == 'QUIT') {
			
			// Check to see if the user which just quit was logged in as an admin
			$IRC_Quit_mySQL_Query = "SELECT * FROM `admins_temp` WHERE `admin` = '$IRC_Msg_Nick'";
			$IRC_Quit_mySQL_Result = mysql_query($IRC_Quit_mySQL_Query);
			$IRC_Quit_mySQL_Number = mysql_num_rows($IRC_Quit_mySQL_Result);
		
			// If the user was, delete the row from the temporary table so they dont stay logged in, security measure
			if ($IRC_Quit_mySQL_Number > 0) {
		
				// Get the admin temp id from the database
				$IRC_Admin_ID_Temp = mysql_result($IRC_Quit_mySQL_Result,0,"id");
				
				// Delete the temporary row in the database, effectivly logs the admin out
				$IRC_Quit_mySQL_Query = "DELETE FROM `admins_temp` WHERE `id` = '$IRC_Admin_ID_Temp'";
				$IRC_Quit_mySQL_Result = mysql_query($IRC_Quit_mySQL_Query);
				
				// Show it in the console
				IRC_Console("Admin","$IRC_Msg_Nick Logged Out (User Quit)");
			
			}
			
			$IRC_Quit = implode(" ", $command_);
			$IRC_Quit = explode("QUIT :", $IRC_Quit);
			$IRC_Quit = $IRC_Quit[1];
			
			// Lookup the ID of the nickname
			$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Msg_Nick","0");
			
			// Show it in the console
			IRC_Console("Svr","[N:$IRC_Nick_ID] $IRC_Msg_Nick Quit - $IRC_Quit");
			
			// Write it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Quit","$IRC_Quit");
		
		}
		
		// This code will be run everytime someone is kicked out a channel
		if ($IRC_Command_Type == 'KICK') {
		
			// Variable Adjustment
			$IRC_Kick_Nick = $command_[3];
			
			// Get the specific id for the channel from the stats database
			$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Msg_Target");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Kick_Nick","$IRC_Channel_ID");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID_2 = IRC_Nick_Lookup("$IRC_Msg_Nick","$IRC_Channel_ID");
			
			// Update the database
			$IRC_Stat_mySQL_Query = "UPDATE `statistics` SET `kicks` = `kicks` + 1 WHERE `nick` = '$IRC_Nick_ID_2' AND `channel` = '$IRC_Channel_ID'";
			$IRC_Stat_mySQL_Result = mysql_query($IRC_Stat_mySQL_Query);
			
			// Update the database
			$IRC_Stat_mySQL_Query = "UPDATE `statistics` SET `kicked` = `kicked` + 1 WHERE `nick` = '$IRC_Nick_ID' AND `channel` = '$IRC_Channel_ID'";
			$IRC_Stat_mySQL_Result = mysql_query($IRC_Stat_mySQL_Query);
			
			// Show it in the console
			IRC_Console("Svr","$IRC_Kick_Nick Kicked from $IRC_Msg_Target by $IRC_Msg_Nick");
			
			// Write it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Kick","$IRC_Nick_ID_2");
			
			$IRC_Server_Nick = IRC_Setting("ServerNick");
			
			// If this is true, the bot got kicked out so lets rejoin the channel
			if ($IRC_Kick_Nick == $IRC_Server_Nick) {
			
				// Send join command to the server
				IRC_Send("JOIN $IRC_Msg_Target");
			
				// Print to the console about it
				IRC_Console("Svr","Re-Joining $IRC_Msg_Target");
			
			}
		
		}
		
	// Do this if the string is 2 words long
	} else if (sizeof($command_) == 2) {
	
		// Extract Command Type and Msg Data
		// $IRC_Command_Type = PING
		$IRC_Command_Type = $command_[0];
		
		// $IRC_Command_Msg = :irc.quakenet.org
		$IRC_Command_Msg = $command_[1];
		
		// Send a PONG reply when the server sends a PING, because otherwise its going to be bad, evil and mean and disconnect us
		if ($IRC_Command_Type == 'PING') {
		
			// Off goes the ping reply
			IRC_Send("PONG $IRC_Command_Msg");
			
			// Display that were playing ping-pong... with words..   to the console
			IRC_Console("Svr","Ping? Pong!");
			
		}
		
	// Do this if the string is 3 words long
	} else if (sizeof($command_) == 3) {
	
		// This code will be run everytime someone joins the channel, I can see how much you care from here
		if ($IRC_Command_Type == 'JOIN') {
	
			// Extract data about the join
			$IRC_Join_Channel = substr($command_[2],1,strlen($command_[2])-1);
			
			// Get the specific id for the channel from the stats database
			$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Join_Channel");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Msg_Nick","$IRC_Channel_ID");
			
			// Update a host for the specific nickname			
			IRC_Host("$IRC_Nick_ID","$IRC_Channel_ID","$IRC_Msg_Host");
			
			// Adjust the Joins Statistic for the specific nickname and channel
			IRC_Stats_Joins("$IRC_Nick_ID","$IRC_Channel_ID");
			
			// Print to the console
			IRC_Console("Svr","[N:$IRC_Nick_ID|C:$IRC_Channel_ID] $IRC_Msg_Nick Joined $IRC_Join_Channel");
			
			// Lets see if there are any addons to be run when someone joins a channel
			IRC_Join_Addon("$IRC_Nick_ID","$IRC_Channel_ID");
			
			// Write it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Join","");
			
		}
		
		// This code will be run everytime someone leaves the channel, the joy!
		if ($IRC_Command_Type == 'PART') {
		
			// Extract data about the part
			$IRC_Part_Channel = $command_[2];
			
			// Get the specific id for the channel from the stats database
			$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Part_Channel");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Msg_Nick","$IRC_Channel_ID");
		
			// Print to the console
			IRC_Console("Svr","[N:$IRC_Nick_ID|C:$IRC_Channel_ID] $IRC_Msg_Nick Parted $IRC_Part_Channel");
			
			// Write it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Part","");
		
		}
		
		if ($IRC_Command_Type == 'NICK') {
		
			$IRC_New_Nick = substr("$command_[2]", 1);
			IRC_Console("Svr","$IRC_Msg_Nick changed nickname to $IRC_New_Nick");
		
		}
	
	}
	
	// Vacate the server on an error, something fucked up..
	if (ereg("^ERROR",$message)) {
	
		$message = substr("$message", 7);
		
		if ($message == "All connections in use") {
		
			// Send quit command to IRC server, flee!
			IRC_Quit("Error has occured - Server is full, please try another");
		
		} else {
		
			// Send quit command to IRC server, flee!
			IRC_Quit("Error has occured - $message");
		
		}	
		
	}
	
	// Part of the reply to a LUSERS request, this one contains toals users and server numbers,
	if ($IRC_Command_Type == 251) {
	
		// Lets adjust the message slightly
		$IRC_LUSERS = explode(" ",$IRC_Msg);
		$IRC_LUSERS_Visible = $IRC_LUSERS[2];
		$IRC_LUSERS_Invisible = $IRC_LUSERS[5];
		$IRC_LUSERS_Total = $IRC_LUSERS_Visible + $IRC_LUSERS_Invisible;
		$IRC_LUSERS_Servers = $IRC_LUSERS[8];
		$IRC_LUSERS_Message[1] = "Total Users: $IRC_LUSERS_Total ($IRC_LUSERS_Invisible Invisible)";
		$IRC_LUSERS_Message[2] = "$IRC_LUSERS_Servers Servers";
	
		$IRC_lusers_mySQL_Query = "INSERT INTO `temp` VALUES ('','Network_Users','$IRC_LUSERS_Message[1]')";
		$IRC_lusers_mySQL_Result = mysql_query($IRC_lusers_mySQL_Query);
		
		$IRC_lusers_mySQL_Query = "INSERT INTO `temp` VALUES ('','Network_Servers','$IRC_LUSERS_Message[2]')";
		$IRC_lusers_mySQL_Result = mysql_query($IRC_lusers_mySQL_Query);
		
	}
	
	
	// Part of the reply to a LUSERS request, this one contains channel totals,
	if ($IRC_Command_Type == 254) {
	
		// Lets adjust the message slightly
		$IRC_LUSERS_Channels = $command_[3];
		
		$IRC_lusers_mySQL_Query = "SELECT * FROM `temp` WHERE `option` = 'Network_Users'";
		$IRC_lusers_mySQL_Result = mysql_query($IRC_lusers_mySQL_Query);
		$IRC_LUSERS_Message[1] = mysql_result($IRC_lusers_mySQL_Result,0,"value");
		
		$IRC_lusers_mySQL_Query = "DELETE FROM `temp` WHERE `option` = 'Network_Users'";
		mysql_query($IRC_lusers_mySQL_Query);
		
		$IRC_lusers_mySQL_Query = "SELECT * FROM `temp` WHERE `option` = 'Network_Servers'";
		$IRC_lusers_mySQL_Result = mysql_query($IRC_lusers_mySQL_Query);
		$IRC_LUSERS_Message[2] = mysql_result($IRC_lusers_mySQL_Result,0,"value");
		
		$IRC_lusers_mySQL_Query = "DELETE FROM `temp` WHERE `option` = 'Network_Servers'";
		mysql_query($IRC_lusers_mySQL_Query);
		
		$IRC_lusers_mySQL_Query = "OPTIMIZE TABLE `temp`";
		mysql_query($IRC_lusers_mySQL_Query);
				
		IRC_Console("Svr","IRC Network Statistics");
		IRC_Console("Svr","$IRC_LUSERS_Message[1]");
		IRC_Console("Svr","$IRC_LUSERS_Message[2] - $IRC_LUSERS_Channels Channels");
		
	}
	
	
	// Vacate the server on connect if primary nickname is in use, someones being an annoying nick stealing peen
	if ($IRC_Command_Type == 433 && $nickcheck == 0) {
	
		// Tell the console that our first nickname is in use
		IRC_Console("Svr","Primary Nickname In Use");
		
		// Get the secondary nickname out the database
		$IRC_Server_NickSecondary = IRC_Setting(ServerNickSecondary);
		
		// Tell the console we are changing nickname
		IRC_Console("Svr","Changing Nickname to '$IRC_Server_NickSecondary'");
		
		// Change the nickname
		IRC_Send("nick $IRC_Server_NickSecondary");
		
		// So we don't keep running this...
		$nickcheck = 1;
		
	}
	
	
	// Part of the reply to a TOPIC request, other half is in 333
	if ($IRC_Command_Type == 332) {
	
		// Lets get the actual topic message out of the raw string
		$IRC_Topic = explode(" :",$IRC_Msg);
		
		// Because of a coding fuckup somewhere weve lost the # from the channel name :P so lets add it back
		$IRC_Channel = "#$IRC_Topic[0]";
		
		// Now we have the channel name, move the topic back into the variable
		$IRC_Topic = $IRC_Topic[1];
		
		// Because channel name is like.. useless, lets get the channel id specified in the database
		$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Channel");
	
		// Update the database with the message
		$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_message` = '$IRC_Topic' WHERE `id` = '$IRC_Channel_ID'";
		$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
		
		unset($IRC_Topic_Set);
		
		// Add it to the log
		IRC_Log("","$IRC_Channel_ID","Topic","$IRC_Topic");
		
		// Show it in the console
		IRC_Console("Tpc","[C:$IRC_Channel_ID] Topic: $IRC_Topic");
	
	}
	
	// Second part of the reply to a TOPIC request, other half is in 332
	if ($IRC_Command_Type == 333) {
	
		// Splitting up the raw topic line so we can get the time it was put up, the user that did it, and what channel this is
		$IRC_Topic = explode(" ",$IRC_Msg);
		
		// Because of a coding fuckup somewhere weve lost the # from the channel name :P so lets add it back
		$IRC_Channel = "#$IRC_Topic[0]";
		
		// Because channel name is like.. useless, lets get the channel id specified in the database
		$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Channel");
		
		// Who set the topic
		$IRC_Topic_Nickname = $IRC_Topic[1];
		
		// Lookup the nickname id for the supposed topic author
		$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Topic_Nickname","$IRC_Channel_ID");
		
		// When it got set
		$IRC_Topic_Set = $IRC_Topic[2];
		
		// Update the database with the time the topic was set
		$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_set` = '$IRC_Topic_Set' WHERE `id` = '$IRC_Channel_ID'";
		$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
		
		// Update the database with what moron set the topic
		$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_author` = '$IRC_Topic_Nickname' WHERE `id` = '$IRC_Channel_ID'";
		$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
		
		// Show it in the console
		IRC_Console("Tpc","[C:$IRC_Channel_ID] Topic Author: $IRC_Topic_Nickname");
		
		// Change the date to something we can all read
		$IRC_Topic_Set = date("d/m/Y H:i",$IRC_Topic_Set);
		
		// And the date
		IRC_Console("Tpc","[C:$IRC_Channel_ID] Topic Date: $IRC_Topic_Set");
		
	}
	
	// 376 is the end of the MOTD, hence we are now connected to the IRC network
	if ($IRC_Command_Type == 376) {
	
		// Tell the console were now connected
		$IRC_Server_Address = IRC_Setting("ServerIP");
		IRC_Console("Svr","Connected to $IRC_Server_Address Successfully");
	
		// Set $nickcheck to 1 so that we bypass the quit on raw message 433 (nickname in use)
		$nickcheck = 1;
		
		// Get the PHP Process ID Number
		$IRC_Bot_PID = getmypid();
		
		// Update the database with the PID of the bot is (its so we can kill off the bot when we want)
		// We do this here because then we know the bot has connected and the PID is valid
		$IRC_PID_mySQL_Query = "UPDATE `settings` SET `value` = '$IRC_Bot_PID' WHERE `setting` = 'PID'";
		$IRC_PID_mySQL_Result = mysql_query($IRC_PID_mySQL_Query);
		
		// Ask the database how many channels there are
		$IRC_Join_mySQL_Query = "SELECT * FROM `channels` ORDER BY `channel` ASC";
		$IRC_Join_mySQL_Result = mysql_query($IRC_Join_mySQL_Query);
		$IRC_Join_mySQL_Number = mysql_num_rows($IRC_Join_mySQL_Result);
		$IRC_Join_mySQL_Temp = 0;
		
		// Lets join all the channels we have in the database shall we
		while ($IRC_Join_mySQL_Number > $IRC_Join_mySQL_Temp) {
		
			// Get the channel name from the database
			$IRC_Join_Channel = mysql_result($IRC_Join_mySQL_Result,$IRC_Join_mySQL_Temp,"channel");
			
			// Get the channel key from the database
			$IRC_Join_Channel_Key = mysql_result($IRC_Join_mySQL_Result,$IRC_Join_mySQL_Temp,"key");
			
			// Join the channel
			IRC_Join("$IRC_Join_Channel","$IRC_Join_Channel_Key");
			
			// Usual bollocks, increase the loop start again.. yada yada yada
			$IRC_Join_mySQL_Temp++;
					
		}
		
	}

?>