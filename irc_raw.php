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
	
	// Get the specific id for the channel from the stats database
	$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Msg_Target");
	
	// Get the specific id for the nickname from the stats database
	$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Msg_Nick","$IRC_Channel_ID");
	
	// Update a host for the specific nickname			
	IRC_Host("$IRC_Nick_ID","$IRC_Channel_ID","$IRC_Msg_Host");

	// Do this if the string is longer than 3 words
	if (sizeof($command_) > 3) {
	
		// The highest number of commands come through here
		if ($IRC_Command_Type == 'PRIVMSG') {
		
			// Explode the message apart to see if its an ACTION or normal message
			$IRC_Msg_ActionCheck = substr($IRC_Msg, 1);
			$IRC_Msg_ActionCheck = explode(" ",$IRC_Msg_ActionCheck);
			
			// If its an action... (case sensitive now, lets not spark it off with someone being a director and shouting action! THIS ISNT A FILM SET FOR GOD SAKE)
			if ($IRC_Msg_ActionCheck[0] === "ACTION") {
			
				// Lets not play around with the main $IRC_Msg, as we might break something if we remove the ACTION from it
				// Plus if we ever want to look at the log, we wont have a clue what ones are ACTIONS and which ones arnt
				// And im bored
				$IRC_Msg_Action = $IRC_Msg;
				$IRC_Msg_Action = substr($IRC_Msg_Action, 8);
				
				// Send it to the console command for printing
				IRC_Console("Msg","[C:$IRC_Channel_ID|N:$IRC_Nick_ID] * $IRC_Msg_Nick $IRC_Msg_Action");
				
			// If its a normal message...
			} else {
			
				// Send it to the console command for printing
				IRC_Console("Msg","[C:$IRC_Channel_ID|N:$IRC_Nick_ID] <$IRC_Msg_Nick> $IRC_Msg");
				
			}
			
			// Add it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Msg","$IRC_Msg");
			
			// Admin Commands
			IRC_Admin($sock,"$IRC_Msg_Nick","$IRC_Msg","$IRC_Msg_Host","$IRC_Admin_Nick","$IRC_Admin_Pass","$IRC_Admin_Host");
			
			// Start the IRC Stats Parser
			IRC_Stats("$IRC_Channel_ID","$IRC_Nick_ID","$IRC_Msg");
			
			// Starts the addon program
			IRC_Addon("$IRC_Nick_ID","$IRC_Channel_ID","$IRC_Msg",$sock);
						
		}
		
		// This code will be run everytime a topic is shown or a topic is changed
		if ($IRC_Command_Type == 'TOPIC') {
		
			// Take off the two new lines
			$IRC_Topic = substr($IRC_Msg,0,-2);
			
			// Get Current Time
			$IRC_Topic_Set = time();
			
			// Update the database with the message
			$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_message` = '$IRC_Topic' WHERE `id` = '$IRC_Channel_ID'";
			$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
			
			// Update the database with the time the topic was set
			$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_set` = '$IRC_Topic_Set' WHERE `id` = '$IRC_Channel_ID'";
			$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
			
			$IRC_Topic_mySQL_Query = "SELECT * FROM `nicks` WHERE `id` = '$IRC_Nick_ID'";
			$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
			$IRC_Nick = mysql_result($IRC_Topic_mySQL_Result,0,"nick");
			
			// Update the database with the author of this cruel creation of a topic
			$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_author` = '$IRC_Nick' WHERE `id` = '$IRC_Channel_ID'";
			$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
			
			// Add it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Topic","$IRC_Topic");
			
			// Show it in the console
			IRC_Console("Tpc","[C:$IRC_Channel_ID|N:$IRC_Nick_ID] $IRC_Topic");
			
			// Show it in the console.. again.. some people eh?
			IRC_Console("Tpc","[C:$IRC_Channel_ID] Topic Author: $IRC_Nick Date: $IRC_Topic_Set");
		
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
		
		}
		
		// This code will be run everytime someone is kicked out a channel
		if ($IRC_Command_Type == 'KICK') {
			
			// Get the specific id for the channel from the stats database
			$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Msg_Target");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID = IRC_Nick_Lookup("$command_[3]","$IRC_Channel_ID");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID_2 = IRC_Nick_Lookup("$IRC_Msg_Nick","$IRC_Channel_ID");
			
			// Update the database
			$IRC_Stat_mySQL_Query = "UPDATE `statistics` SET `kicks` = `kicks` + 1 WHERE `nick` = '$IRC_Nick_ID_2' AND `channel` = '$IRC_Channel_ID'";
			$IRC_Stat_mySQL_Result = mysql_query($IRC_Stat_mySQL_Query);
			
			// Update the database
			$IRC_Stat_mySQL_Query = "UPDATE `statistics` SET `kicked` = `kicked` + 1 WHERE `nick` = '$IRC_Nick_ID' AND `channel` = '$IRC_Channel_ID'";
			$IRC_Stat_mySQL_Result = mysql_query($IRC_Stat_mySQL_Query);
			
			// Show it in the console
			IRC_Console("Svr","$command_[3] Kicked from $IRC_Msg_Target by $IRC_Msg_Nick");
			
			// Write it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Kick","$IRC_Nick_ID_2");
		
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
			IRC_Send($sock,"PONG $IRC_Command_Msg");
			
			// Display that were playing ping-pong... with words..   to the console
			IRC_Console("Svr","Ping? Pong!");
			
		}
		
	// Do this if the string is 3 words long
	} else if (sizeof($command_) == 3) {
	
		// This code will be run everytime someone joins the channel, I can see how much you care from here
		if ($IRC_Command_Type == 'JOIN') {
	
			// Extract data about the join
			$IRC_Join_Channel = substr($command_[2],1,strlen($command_[2])-1);
			$IRC_Join_Channel = substr($IRC_Join_Channel,0,-2);
			
			// Get the specific id for the channel from the stats database
			$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Join_Channel");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Msg_Nick","$IRC_Channel_ID");
			
			// Update a host for the specific nickname			
			IRC_Host("$IRC_Nick_ID","$IRC_Channel_ID","$IRC_Msg_Host");
			
			// Adjust the Joins Statistic for the specific nickname and channel
			IRC_Stats_Joins("$IRC_Nick_ID","$IRC_Channel_ID");
			
			// Print to the console
			IRC_Console("Svr","[N:$IRC_Nick_ID|C:$IRC_Channel_ID] $IRC_Msg_Nick Joined");
			
			// Lets see if there are any addons to be run when someone joins a channel
			IRC_Join_Addon("$IRC_Nick_ID","$IRC_Channel_ID",$sock);
			
			// Write it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Join","");
			
		}
		
		// This code will be run everytime someone leaves the channel, the joy!
		if ($IRC_Command_Type == 'PART') {
		
			// Extract data about the part
			$IRC_Part_Channel = $command_[2];
			$IRC_Part_Channel = substr($IRC_Part_Channel,0,-2);
			
			// Get the specific id for the channel from the stats database
			$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Part_Channel");
			
			// Get the specific id for the nickname from the stats database
			$IRC_Nick_ID = IRC_Nick_Lookup("$IRC_Msg_Nick","$IRC_Channel_ID");
		
			// Print to the console
			IRC_Console("Svr","[N:$IRC_Nick_ID|C:$IRC_Channel_ID] $IRC_Msg_Nick Parted");
			
			// Write it to the log
			IRC_Log("$IRC_Nick_ID","$IRC_Channel_ID","Part","");
		
		}
	
	}
	
	// Vacate the server on an error, something fucked up..
	if (ereg("^ERROR",$message)) {
	
		// Send quit command to IRC server, flee!
		IRC_Quit($sock,"Error has occured");
		
	}
	
	// Vacate the server on connect if primary nickname is in use, someones being an annoying nick stealing peen
	if ($IRC_Command_Type == 433 && $nickcheck == 0) {
	
		// Send quit command to IRC server, swim away!
		IRC_Quit($sock,"Primary Nickname In Use");
		
	}
	
	// Part of the reply to a TOPIC request, other half is in 333
	if ($IRC_Command_Type == 332) {
	
		// Lets get the actual topic message out of the raw string
		$IRC_Topic = explode(":",$IRC_Msg);
		
		// Because of a coding fuckup somewhere weve lost the # from the channel name :P so lets add it back
		$IRC_Channel = "#$IRC_Topic[0]";
		
		// Because channel name is like.. useless, lets get the channel id specified in the database
		$IRC_Channel_ID = IRC_Channel_Lookup("$IRC_Channel");
	
		// Take off the two new lines
		$IRC_Topic = substr($IRC_Topic[1],0,-2);
		
		// Add some slashes so that there wont be problems with quote marks and evil shit like that
		$IRC_Topic = addslashes($IRC_Topic);
			
		// Update the database with the message
		$IRC_Topic_mySQL_Query = "UPDATE `channels` SET `topic_message` = '$IRC_Topic' WHERE `id` = '$IRC_Channel_ID'";
		$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
		
		unset($IRC_Topic_Set);
		
		// Add it to the log
		IRC_Log("","$IRC_Channel_ID","Topic","$IRC_Topic");
		
		// Show it in the console
		IRC_Console("Tpc","[C:$IRC_Channel_ID] $IRC_Topic");
	
	}
	
	// Second part of the reply to a TOPIC request, other half is in 332
	if ($IRC_Command_Type == 333) {
	
		// Splitting up the raw topic line so we can get the time it was put up, the user that did it, and what channel this is
		$IRC_Topic = explode(" ",$IRC_Msg);
		
		// Removing the two new lines again
		$IRC_Topic[2] = substr($IRC_Topic[2],0,-2);
		
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
		IRC_Console("Tpc","[C:$IRC_Channel_ID] Topic Author: $IRC_Topic_Nickname Date: $IRC_Topic_Set");
		
	}
	
	// 376 is the end of the MOTD, hence we are now connected to the IRC network
	if ($IRC_Command_Type == 376) {
	
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
			
			// Send join command to the server
			IRC_Send($sock,"JOIN $IRC_Join_Channel");
			
			// Print to the console about it
			IRC_Console("Svr","Joining $IRC_Join_Channel");
			
			// Usual bollocks, increase the loop start again.. yada yada yada
			$IRC_Join_mySQL_Temp++;
					
		}
		
	}
	
	
	
?>