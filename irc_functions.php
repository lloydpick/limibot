<?php

	// Send an action to the target with message
	function IRC_Action($sock,$IRC_Target,$IRC_Message) {

		// Send the data across the socket
		IRC_Send($sock,"PRIVMSG $IRC_Target :\001ACTION $IRC_Message");

		// Print it to the console
		IRC_Console("Msg","ACTION --> $IRC_Target - $IRC_Message");

	}

	// Checks for, and then runs any addons
	function IRC_Addon($IRC_Nick_ID,$IRC_Channel_ID,$IRC_Msg,$sock) {

		// Get the number of addons in the system
		$IRC_Addons_mySQL_Query = "SELECT * FROM `commands` WHERE `active` = 1";
		$IRC_Addons_mySQL_Result = mysql_query($IRC_Addons_mySQL_Query);
		$IRC_Addons_mySQL_Number = mysql_num_rows($IRC_Addons_mySQL_Result);
		$IRC_Addons_Temp_1 = 0;
		$IRC_Addons_Temp_2 = explode(" ",$IRC_Msg);
		$IRC_Addons_Command_Reqested = $IRC_Addons_Temp_2[0];

		// Lets see if there were any matches
		while ($IRC_Addons_mySQL_Number > $IRC_Addons_Temp_1) {

			// Cycle through the commands until one is matched
			$IRC_Addons_Command = mysql_result($IRC_Addons_mySQL_Result,$IRC_Addons_Temp_1,"command");

			// If a command match is found...
			if ($IRC_Addons_Command_Reqested == $IRC_Addons_Command) {

				// Get the addon filename and then print to the console, and then include the working file
				$IRC_Addons_Command_File = mysql_result($IRC_Addons_mySQL_Result,$IRC_Addons_Temp_1,"filename");
				$IRC_Nick = IRC_Nick_Lookup_ID("$IRC_Nick_ID");

				// Someone triggered this from a channel..  but what channel?
				if($IRC_Channel_ID != 0) {

					$IRC_Channel = IRC_Channel_Lookup_ID("$IRC_Channel_ID");

				// Someone triggered this from a private message.. bloody peen, blatently some script kiddie haxor
				} else {

					$IRC_Channel = "a Private Message";

				}

				IRC_Console("Adn","Addon $IRC_Addons_Command_Reqested requested by $IRC_Nick in $IRC_Channel");
				include("./addons/$IRC_Addons_Command_File");

			}

			// Increase the temp loop value
			$IRC_Addons_Temp_1++;

		}

	}

	// Retrieve information about an addon from the database
	function IRC_Addon_Info($IRC_Addons_Filename) {

		// Lookup the filename
		$IRC_Addons_mySQL_Query = "SELECT * FROM `commands` WHERE `filename` = '$IRC_Addons_Filename'";
		$IRC_Addons_mySQL_Result = mysql_query($IRC_Addons_mySQL_Query);
		$IRC_Addons_mySQL_Number = mysql_num_rows($IRC_Addons_mySQL_Result);

		// If we have a command with that filename
		if ($IRC_Addons_mySQL_Number > 0) {

			// Offload all the info into the array $IRC_Addons
			$IRC_Addons[id] = mysql_result($IRC_Addons_mySQL_Result,0,"id");
			$IRC_Addons[active] = mysql_result($IRC_Addons_mySQL_Result,0,"active");
			$IRC_Addons[command] = mysql_result($IRC_Addons_mySQL_Result,0,"command");
			$IRC_Addons[description] = mysql_result($IRC_Addons_mySQL_Result,0,"description");
			$IRC_Addons[usage] = mysql_result($IRC_Addons_mySQL_Result,0,"usage");
			$IRC_Addons[filename] = mysql_result($IRC_Addons_mySQL_Result,0,"filename");

			// Send it back
			return $IRC_Addons;

		// No match for the filename specified was found
		} else {

			// This is so we know nothing was found
			$IRC_Addons[id] = 0;

			// Send it back
			return $IRC_Addons;

		}

	}

	// Admin Commands
	function IRC_Admin($sock,$IRC_Msg_Nick,$IRC_Msg,$IRC_Msg_Host,$IRC_Admin_Nick,$IRC_Admin_Pass,$IRC_Admin_Host) {

		// Include the file so that we can edit on the fly
		include ("irc_admin.php");

	}

	// Check to see if the Admin has access to a specific command
	function IRC_Admin_Allowed($IRC_Command,$IRC_Nick,$IRC_Msg,$sock) {

		// Get the account the admin is currently logged into
		$IRC_Admin_mySQL_Query = "SELECT * FROM `admins_temp` WHERE `admin` = '$IRC_Nick'";
		$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
		$IRC_Admin_Account = mysql_result($IRC_Admin_mySQL_Result,0,"account");

		// Get the command ID that the admin is attempting to access
		$IRC_Admin_mySQL_Query = "SELECT * FROM `admins_commands` WHERE `command` = '$IRC_Command'";
		$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
		$IRC_Admin_CommandID = mysql_result($IRC_Admin_mySQL_Result,0,"id");

		// Now check to see if the admin has access to use the specified command
		$IRC_Admin_mySQL_Query = "SELECT * FROM `admins` WHERE `nick` = '$IRC_Admin_Account' AND `access` LIKE '% $IRC_Admin_CommandID %'";
		$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
		$IRC_Admin_mySQL_Number = mysql_num_rows($IRC_Admin_mySQL_Result);

		// If the admin does have access
		if($IRC_Admin_mySQL_Number == 1) {

			// Print to the console about whats happened
			IRC_Console("Admin","Access Granted for $IRC_Nick, Command '$IRC_Command'");

			// Write this to the admin log
			IRC_Admin_Log("$IRC_Nick","$IRC_Msg");

			// This is sent so the command can be run
			return 1;

		// If the admin does NOT have access
		} else {

			// Print to the console about whats happened
			IRC_Console("Admin","Access Denied for $IRC_Nick, Command '$IRC_Command'");

			// Tell the admin about it
			IRC_Message($sock,"$IRC_Nick","Access Denied");

			// Write this to the admin log
			IRC_Admin_Log("$IRC_Nick","ACCESS DENIED - $IRC_Msg");

			// This is sent so the command can't be run
			return 0;

		}

	}

	// Adds the chat line to the mySQL log
	function IRC_Admin_Log($IRC_Admin_ID,$IRC_Msg) {

		// Get the admin account in use and the admins nickname
		$IRC_Log_mySQL_Query = "SELECT * FROM `admins_temp` WHERE `admin` = '$IRC_Admin_ID'";
		$IRC_Log_mySQL_Result = mysql_query($IRC_Log_mySQL_Query);
		$IRC_Admin_Name = mysql_result($IRC_Log_mySQL_Result,0,"admin");
		$IRC_Admin_Acc = mysql_result($IRC_Log_mySQL_Result,0,"account");
		$IRC_Log_mySQL_Query = "SELECT * FROM `admins` WHERE `nick` = '$IRC_Admin_Acc'";
		$IRC_Log_mySQL_Result = mysql_query($IRC_Log_mySQL_Query);
		$IRC_Admin_Acc = mysql_result($IRC_Log_mySQL_Result,0,"id");
		$IRC_Admin_ID = IRC_Nick_Lookup("$IRC_Admin_Name","0");

		// Insert it into the log so errors, or security breaches can be checked up on
		$IRC_Log_mySQL_Query = "INSERT INTO `logs_admin` (`time`,`acc`,`nick`,`log`) VALUES (NOW(),'$IRC_Admin_Acc','$IRC_Admin_ID','$IRC_Msg')";
		$IRC_Log_mySQL_Result = mysql_query($IRC_Log_mySQL_Query);

	}

	// Lookup the channel's id from the stats database
	function IRC_Channel_Lookup($IRC_Msg_Target) {

		// mySQL Query
		$IRC_mySQL_Query = "SELECT * FROM `channels` WHERE `channel` = '$IRC_Msg_Target'";

		// mySQL Result
		$IRC_mySQL_Result = mysql_query($IRC_mySQL_Query);
		$IRC_mySQL_Number = mysql_num_rows($IRC_mySQL_Result);

		if ($IRC_mySQL_Number > 0) {

			// Return the channel ID to the command
			return mysql_result($IRC_mySQL_Result,0,"id");

		} else {

			return 0;

		}

	}

	// Lookup the channel's name from its id number
	function IRC_Channel_Lookup_ID($IRC_Msg_Target) {

		// mySQL Query
		$IRC_mySQL_Query = "SELECT * FROM `channels` WHERE `id` = '$IRC_Msg_Target'";

		// mySQL Result
		$IRC_mySQL_Result = mysql_query($IRC_mySQL_Query);
		$IRC_mySQL_Number = mysql_num_rows($IRC_mySQL_Result);

		if ($IRC_mySQL_Number > 0) {

			// Return the channel ID to the command
			return mysql_result($IRC_mySQL_Result,0,"channel");

		} else {

			return 0;

		}

	}

	// Prints information to the console
	function IRC_Console($type,$message) {

		// This is so we dont print blank lines
		if ($message != "") {

			// Get the currect time
			$date = date("H:i:s");

			// Print to the console string
			$echo = "[$date] ($type) $message \n";

			// Print to the console
			echo "$echo";

		}

	}

	// Makes the console look nice :)
	function IRC_Console_Title_Start($title) {
		$echo = "$title \n________________________ \n";


		// Print to the console
		echo "$echo";

	}

	// Makes the console look nice :)
	function IRC_Console_Title_End() {
		$echo = "________________________\n\n";

		// Print to the console
		echo "$echo";

	}

	// Send a ctcp request to the target
	function IRC_Ctcp($sock,$IRC_Target,$IRC_Type) {

		if ($IRC_Type == "VERSION") {

			IRC_Send($sock,"PRIVMSG $IRC_Target :\001VERSION");

		} else {

			IRC_Send($sock,"NOTICE $IRC_Target :\001$IRC_Type");

		}

		// Send the data across the socket
		IRC_Send($sock,"PRIVMSG $IRC_Target :\001$IRC_Type");

		// Print it to the console
		IRC_Console("Ctp","CTCP $IRC_Type --> $IRC_Target");

	}

	// Updates a host for the specififc nickname and channel
	function IRC_Host($IRC_Nick_ID,$IRC_Channel_ID,$IRC_Msg_Host) {

		// Update the database
		$IRC_Host_mySQL_Query = "UPDATE `statistics` SET `host` = '$IRC_Msg_Host' WHERE `nick` = '$IRC_Nick_ID' AND `channel` = '$IRC_Channel_ID'";
		$IRC_Host_mySQL_Result = mysql_query($IRC_Host_mySQL_Query);

	}

	// Print out various information when the bot boots
	function IRC_Info($IRC_Server_IP,$IRC_Server_Port,$IRC_Server_Pass,$IRC_Server_Nick) {

		// Here we will wipe the current PID we have in the database for the bot because well.. its stale and going moldy
		$IRC_PID_mySQL_Query = "UPDATE `settings` SET `value` = '' WHERE `setting` = 'PID'";
		$IRC_PID_mySQL_Result = mysql_query($IRC_PID_mySQL_Query);

		// Get the PHP Process ID Number (Just for user information, it doesnt get stored anywhere till later on, like when it may be half useful)
		$IRC_Bot_PID = getmypid();

		// LimiBot Version
		$IRC_LimiBot_Version = IRC_Setting("Version");

		// Empty the logged in admins table so that if the bot crashed out no admins are still logged in
		$IRC_Admin_mySQL_Query = "TRUNCATE TABLE `admins_temp`";
		$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);

		// Time for the bit that looks nice and purty :)
		IRC_Logo();
		IRC_Console("Info >> Name","LimiBot");
		IRC_Console("Info >> Desc","PHP IRC Bot");
		IRC_Console("Info >> Version","$IRC_LimiBot_Version");
		IRC_Console("Info >> Creator","Limited Edition!");
		IRC_Console("Info >> E-Mail","lloydpick@users.sourceforge.net");
		IRC_Console("Info >> Web-Site","https://sourceforge.net/projects/limibot/ \n");
		IRC_Console("Server >> Addr","$IRC_Server_IP");
		IRC_Console("Server >> Port","$IRC_Server_Port");
		IRC_Console("Server >> Pass","$IRC_Server_Pass");
		IRC_Console("Server >> Nick","$IRC_Server_Nick \n");
		IRC_Console("PHP >> PID","$IRC_Bot_PID \n");
		IRC_Console("Svr","Temporary Administration Login Tables Wiped");

	}

	// Checks for, and then runs any addons
	function IRC_Join_Addon($IRC_Nick_ID,$IRC_Channel_ID,$sock) {

		// Get the number of addons in the system
		$IRC_Addons_mySQL_Query = "SELECT * FROM `commands_joins` WHERE `active` = 1";
		$IRC_Addons_mySQL_Result = mysql_query($IRC_Addons_mySQL_Query);
		$IRC_Addons_mySQL_Number = mysql_num_rows($IRC_Addons_mySQL_Result);

		// Unset the temp variable, because with the amount of copying of code going on round here, its blatently going to contain a value and break shit
		unset($IRC_Addons_Temp_1);
		$IRC_Addons_Temp_1 = 0;

		// Lets see if there were any matches
		while ($IRC_Addons_mySQL_Number > $IRC_Addons_Temp_1) {

			// Get the addon filename and then print to the console, and then include the working file
			$IRC_Addons_Command_Name = mysql_result($IRC_Addons_mySQL_Result,$IRC_Addons_Temp_1,"name");
			$IRC_Addons_Command_File = mysql_result($IRC_Addons_mySQL_Result,$IRC_Addons_Temp_1,"filename");

			// While were here we might aswell just get thier real nickname.. you know, just for laughs
			$IRC_Nick = IRC_Nick_Lookup_ID("$IRC_Nick_ID");

			// Get the channel name where the nickname joined, because thats just a tad important.. some disagree though
			$IRC_Channel = IRC_Channel_Lookup_ID("$IRC_Channel_ID");

			// Wonder if anyone is actually reading all these... well.. anyway
			// You've now got $IRC_Channel_ID $IRC_Channel $IRC_Nick_ID $IRC_Nick available for you addon file

			// I have also commented out the IRC_Console command below.. well because otherwise its gonna spam the console
			// IRC_Console("Adn","Addon $IRC_Addons_Command_Name being run for $IRC_Nick in $IRC_Channel");

			// Muldoon wants to cock me at every occasion.. don't believe me? #mpukhosting on quakenet and ask for squire and his fluffy feather
			// Oh yeah, while were on track and doing what were meant to be doing, lets actually include the addon file
			include("./addons/joins/$IRC_Addons_Command_File");

			// Increase the temp loop value, because there might be more than one on join addon! SHOCK HORROR!
			$IRC_Addons_Temp_1++;

		}

	}

	// Retrieve information about an join addon from the database
	function IRC_Join_Addon_Info($IRC_Addons_Filename) {

		// Lookup the filename
		$IRC_Addons_mySQL_Query = "SELECT * FROM `commands_joins` WHERE `filename` = '$IRC_Addons_Filename'";
		$IRC_Addons_mySQL_Result = mysql_query($IRC_Addons_mySQL_Query);
		$IRC_Addons_mySQL_Number = mysql_num_rows($IRC_Addons_mySQL_Result);

		// If we have a command with that filename
		if ($IRC_Addons_mySQL_Number > 0) {

			// Offload all the info into the array $IRC_Addons
			$IRC_Join_Addons[id] = mysql_result($IRC_Addons_mySQL_Result,0,"id");
			$IRC_Join_Addons[active] = mysql_result($IRC_Addons_mySQL_Result,0,"active");
			$IRC_Join_Addons[name] = mysql_result($IRC_Addons_mySQL_Result,0,"name");
			$IRC_Join_Addons[description] = mysql_result($IRC_Addons_mySQL_Result,0,"description");
			$IRC_Join_Addons[filename] = mysql_result($IRC_Addons_mySQL_Result,0,"filename");

			// Send it back
			return $IRC_Join_Addons;

		// No match for the filename specified was found
		} else {

			// This is so we know nothing was found
			$IRC_Join_Addons[id] = 0;

			// Send it back
			return $IRC_Join_Addons;

		}

	}


	// Adds the chat line to the mySQL log
	function IRC_Log($IRC_Nick_ID,$IRC_Channel_ID,$IRC_Log_Type,$IRC_Msg) {

		// Check to make sure its not a admin logging in with passwords that could be stolen etc
		if ($IRC_Channel_ID != 0) {

			// mySQL Insert Command
			$IRC_Log_mySQL_Query = "INSERT INTO `logs` (`time`,`nick`,`channel`,`type`,`log`) VALUES (NOW(),'$IRC_Nick_ID','$IRC_Channel_ID','$IRC_Log_Type','$IRC_Msg')";
			$IRC_Log_mySQL_Result = mysql_query($IRC_Log_mySQL_Query);

		}

	}

	// Makes the console look nice :) (Please don't edit or remove this!!!)
	function IRC_Logo() {

		// Print the LimiBot logo to the console
		echo "    __    _           _ ____        __ \n";
		echo "   / /   (_)___ ___  (_) __ )____  / /_\n";
		echo "  / /   / / __ `__ \/ / __  / __ \/ __/\n";
		echo " / /___/ / / / / / / / /_/ / /_/ / /_  \n";
		echo "/_____/_/_/ /_/ /_/_/_____/\____/\__/  \n";
		echo "\n";

	}


	// Send a message to the target with message
	function IRC_Message($sock,$IRC_Target,$IRC_Message) {

		// Send the data across the socket
		IRC_Send($sock,"PRIVMSG $IRC_Target :$IRC_Message");

		// Print it to the console
		IRC_Console("Msg","PRIVMSG --> $IRC_Target - $IRC_Message");

	}

	// Connect to the mySQL Database
	function IRC_mySQL_Connect($IRC_mySQL_Info_Server,$IRC_mySQL_Info_User,$IRC_mySQL_Info_Pass,$IRC_mySQL_Info_DataB) {

		// mySQL Connector
		$IRC_mySQL_String = mysql_connect("$IRC_mySQL_Info_Server", "$IRC_mySQL_Info_User", "$IRC_mySQL_Info_Pass");

		// Select the correct mySQL database
		mysql_select_db("$IRC_mySQL_Info_DataB",$IRC_mySQL_String);

	}

	// Lookup the nickname's id from the stats database
	function IRC_Nick_Lookup($IRC_Msg_Nick,$IRC_Channel_ID) {

		$IRC_mySQL_Query_1 = "SELECT * FROM `nicks` WHERE `nick` = '$IRC_Msg_Nick'";
		$IRC_mySQL_Result_1 = mysql_query($IRC_mySQL_Query_1);
		$IRC_mySQL_Temp = mysql_num_rows($IRC_mySQL_Result_1);

		// Check to see if any nicknames were returned
		if ($IRC_mySQL_Temp > 0) {

			// Nickname was found
			// Return the nickname ID to the command
			$IRC_Nick_ID = mysql_result($IRC_mySQL_Result_1,0,"id");
			return $IRC_Nick_ID;

		} else {

			// Nickname not found
			// Insert the nickname to the database if it isnt found
			$IRC_mySQL_Query_2 = "INSERT INTO `nicks` (nick) VALUES ('$IRC_Msg_Nick')";
			$IRC_mySQL_Result_2 = mysql_query($IRC_mySQL_Query_2);

			// Get the id of the nickname we just added
			$IRC_mySQL_Query_3 = "SELECT * FROM `nicks` WHERE `nick` = '$IRC_Msg_Nick'";
			$IRC_mySQL_Result_3 = mysql_query($IRC_mySQL_Query_3);

			// Return the nickname ID to the command
			$IRC_Nick_ID = mysql_result($IRC_mySQL_Result_3,0,"id");

			if ($IRC_Channel_ID != 0) {

				// Use this unique id and add a row to a diffrerent table
				$IRC_mySQL_Query_4 = "INSERT INTO `statistics` (nick,channel) VALUES ('$IRC_Nick_ID','$IRC_Channel_ID')";
				$IRC_mySQL_Result_4 = mysql_query($IRC_mySQL_Query_4);

			}

			return $IRC_Nick_ID;

		}

	}

	// Lookup a nickname from its ID number
	function IRC_Nick_Lookup_ID($IRC_NickLookup_ID) {

		$IRC_mySQL_Query_1 = "SELECT * FROM `nicks` WHERE `id` = '$IRC_NickLookup_ID'";
		$IRC_mySQL_Result_1 = mysql_query($IRC_mySQL_Query_1);
		$IRC_mySQL_Number_1 = mysql_num_rows($IRC_mySQL_Result_1);

		if ($IRC_mySQL_Number_1 > 0) {

			// Return the nick to the command
			return mysql_result($IRC_mySQL_Result_1,0,"nick");

		} else {

			return 0;

		}

	}

	// Send a notice to the target with message
	function IRC_Notice($sock,$IRC_Target,$IRC_Message) {

		// Send the data across the socket
		IRC_Send($sock,"NOTICE $IRC_Target :$IRC_Message");

		// Print it to the console
		IRC_Console("Msg","NOTICE --> $IRC_Target - $IRC_Message");

	}


	// Quits the IRC network
	function IRC_Quit($sock,$reason) {

		// Send the data across the socket
		IRC_Send($sock,"quit $reason");

		// Print quit info to console
		IRC_Console("Quit","$reason");

		// Exit the php file so it restarts when using the bat file to start
		exit;

	}


	// Raw IRC Parser
	function IRC_Raw($message,$sock,$IRC_Admin_Nick,$IRC_Admin_Pass,$IRC_Admin_Host) {

		// Include the file so that we can edit on the fly
		include ("irc_raw.php");

	}


	// Allows commands to be sent to the server
	function IRC_Send($sock,$message) {

		// Send the data across the socket
		fputs($sock,"$message \r\n");

	}


	// Get IRC Server Settings from the mySQL database
	function IRC_Setting($IRC_Server_Setting) {

		// Get the Servers IP
		$IRC_Settings_mySQL_Query = "SELECT * FROM `settings` WHERE `setting` = '$IRC_Server_Setting'";
		$IRC_Settings_mySQL_Result = mysql_query($IRC_Settings_mySQL_Query);
		$IRC_Setting_mySQL_Number = mysql_num_rows($IRC_Settings_mySQL_Result);

		if ($IRC_Setting_mySQL_Number > 0) {

			return mysql_result($IRC_Settings_mySQL_Result,0,"value");

		}

	}


	// The main function
	function IRC_Start($IRC_Server_IP,$IRC_Server_Port,$IRC_Server_Pass,$IRC_Server_Nick,$IRC_Admin_Nick,$IRC_Admin_Pass,$IRC_Admin_Host) {

		// Attempts to Connect to the IRC Server
		$sock = fsockopen("$IRC_Server_IP","$IRC_Server_Port");

		// $loop is added so we can perform startup operations
		$loop = 1;

		// $nickcheck added so we can quit the network if the bots nickname is already in use
		$nickcheck = 0;

		// If the socket didnt connect, break out
		if(!$sock) {

			IRC_Console("Error","Socket Could not Connect");
			break;

		}

		// If the socket connected properly...
		else {

			// Aaaaaaaaaaaand were away! - Update the database with the time when the bot connected to irc
			$IRC_BotStart = time();

			// If for some insane reason youd rather have a mySQL timestamp in here (freak), uncomment the next line (No complaining that I did'nt give you an option too either)
			// $IRC_BotStart = date(YmdGis);
			$IRC_Settings_mySQL_Query = "UPDATE `settings` SET `value` = $IRC_BotStart WHERE `setting` = 'BotStart'";
			$IRC_Settings_mySQL_Result = mysql_query($IRC_Settings_mySQL_Query);

			// Start the main number loop, this is really fucking evil and dumb, i'v got to find another way of doing this tbh
			while ($loop > 0) {

				// Get data from the socket and print it to the console
				$irc = fgets($sock, 1024);

				// First things first, lets bin the two new lines at the end of the string
				$irc = substr($irc, 0, -2);

				// Check to see if we write the raw irc to the console
				$IRC_ShowRaw = IRC_Setting(ShowRaw);

				// Do we show the raw?
				if($IRC_ShowRaw == 1) {

					// Show the raw incoming code
					IRC_Console("Raw","$irc");

				}

				// This is where we place things we only want to be sent once to the server at startup
				if ($loop == 2) {

					IRC_Console("Svr","Sending Password");
					IRC_Send($sock,"pass $IRC_Server_Pass");
					IRC_Console("Svr","Setting Nick");
					IRC_Send($sock,"nick $IRC_Server_Nick");
					IRC_Console("Svr","Registering (This may take up to 2minutes)");
					IRC_Send($sock,"user $IRC_Server_Nick $IRC_Server_IP $IRC_Server_IP LimiBot");

				}

				// Place NickServ Login Here
				if ($loop == 10) {

					// Get the specific command to identify from the database
					$IRC_Admin_NickIdentify = IRC_Setting(NickIdentify);

					// Send it to the IRC server
					IRC_Send($sock,"$IRC_Admin_NickIdentify");

					// Print to the console that the command was sent
					IRC_Console("Svr","Identified to NickServ");

				}

				// Send the raw irc string to the parser
				IRC_Raw($irc,$sock,"$IRC_Admin_Nick","$IRC_Admin_Pass","$IRC_Admin_Host");

				// Start the loop again
				$loop++;

				// To increase speed
				fflush($sock);
				flush();

			}

		}

	}


	// Stats Parser
	function IRC_Stats($IRC_Msg_Target,$IRC_Msg_Nick,$IRC_Msg) {

		// Include the file so that we can edit on the fly
		include ("irc_stats.php");

	}


	// Update the Joins Statistic
	function IRC_Stats_Joins($IRC_Nick_ID,$IRC_Channel_ID) {

		// Update the database
		$IRC_Stat_mySQL_Query = "UPDATE `statistics` SET `joins` = `joins` + 1 WHERE `nick` = '$IRC_Nick_ID' AND `channel` = '$IRC_Channel_ID'";
		$IRC_Stat_mySQL_Result = mysql_query($IRC_Stat_mySQL_Query);

	}


	// Retrieve the topic for the specified channel id
	function IRC_Topic($IRC_Channel_ID) {

		// Get the topic for the channel
		$IRC_Topic_mySQL_Query = "SELECT * FROM `channels` WHERE `id` = '$IRC_Channel_ID'";
		$IRC_Topic_mySQL_Result = mysql_query($IRC_Topic_mySQL_Query);
		$IRC_Topic_mySQL_Number = mysql_num_rows($IRC_Topic_mySQL_Result);

		if ($IRC_Topic_mySQL_Number > 0) {

			$topic[message] = mysql_result($IRC_Topic_mySQL_Result,0,"topic_message");
			$topic[author] = mysql_result($IRC_Topic_mySQL_Result,0,"topic_author");
			$topic[timeset] = mysql_result($IRC_Topic_mySQL_Result,0,"topic_set");

			return $topic;

		}

	}

?>
