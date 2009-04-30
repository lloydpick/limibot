<?php
	
	// Lets see what nickname they want stats about
	$IRC_StatsLookup_Nick = $IRC_Addons_Temp_2[1];
	
	// Did they actually input a nickname? if not...
	if($IRC_StatsLookup_Nick == "") {
	
		IRC_Send($sock,"NOTICE $IRC_Nick :Please specify a nickname - !stats <nickname>");
	
	// If they did input a nickname...
	} else {
	
		// Lets grab the requested nicknames id's from the nicks table
		$IRC_NickLookup_1 = "SELECT * FROM `nicks` WHERE `nick` = '$IRC_StatsLookup_Nick'";
		$IRC_NickLookup_Result_1 = mysql_query($IRC_NickLookup_1);
		$IRC_NickLookup_Temp = mysql_num_rows($IRC_NickLookup_Result_1);
	
		// Did that exist?
		if ($IRC_NickLookup_Temp > 0) {
		
			// If it did, get the id number
			$IRC_NickLookup_ID = mysql_result($IRC_NickLookup_Result_1,0,"id");
			$IRC_StatsLookup_NickID = $IRC_NickLookup_ID;
			
		}
		
		// Did it bring back an ID? If it did'nt...		
		if ($IRC_StatsLookup_NickID == "") {
		
			IRC_Send($sock,"NOTICE $IRC_Nick :I don't recognise that nickname, please try another - !stats <nickname>");
		
		// If it did bring back an ID number...
		} else {
		
			// Now lets go and grab the stats information from the statistics table based on nickname id and the channel id it was requested from
			$IRC_mySQL_Query_1 = "SELECT * FROM `statistics` WHERE `nick` = '$IRC_StatsLookup_NickID' AND `channel` = '$IRC_Channel_ID'";
			$IRC_mySQL_Result_1 = mysql_query($IRC_mySQL_Query_1);
			$IRC_mySQL_Temp = mysql_num_rows($IRC_mySQL_Result_1);
			
			// Any results? If so...
			if ($IRC_mySQL_Temp > 0) {
	
				$IRC_StatsLookup_Joins = mysql_result($IRC_mySQL_Result_1,0,"joins");
				$IRC_StatsLookup_Typing = mysql_result($IRC_mySQL_Result_1,0,"typing");
				$IRC_StatsLookup_Characters = mysql_result($IRC_mySQL_Result_1,0,"characters");
				$IRC_StatsLookup_Words = mysql_result($IRC_mySQL_Result_1,0,"words");
				$IRC_StatsLookup_Lines = mysql_result($IRC_mySQL_Result_1,0,"lines");
				
				// These dont work yet
				$IRC_StatsLookup_Bans = mysql_result($IRC_mySQL_Result_1,0,"bans");
				$IRC_StatsLookup_Banned = mysql_result($IRC_mySQL_Result_1,0,"banned");
				$IRC_StatsLookup_UnBans = mysql_result($IRC_mySQL_Result_1,0,"unbans");
				$IRC_StatsLookup_UnBanned = mysql_result($IRC_mySQL_Result_1,0,"unbanned");
				$IRC_StatsLookup_Kicks = mysql_result($IRC_mySQL_Result_1,0,"kicks");
				$IRC_StatsLookup_Kicked = mysql_result($IRC_mySQL_Result_1,0,"kicked");
				
				// Work out the duration of the typing
				$seconds = $IRC_StatsLookup_Typing % 60;
				$result2 = (int) ($IRC_StatsLookup_Typing / 60);
				$minutes = $result2 % 60;
				$result2 = (int) ($result2 / 60);
				$hours = $result2 % 24;
				$days = (int) ($result2 / 24);
				$IRC_StatsLookup_Typing = $days."d ".$hours."h ".$minutes."m ".$seconds."s";
				
				// Send the info back to the people who requested it in notice form
				IRC_Send($sock,"NOTICE $IRC_Nick :IRC Statistics for '$IRC_StatsLookup_Nick'");
				IRC_Send($sock,"NOTICE $IRC_Nick :Time Typing: $IRC_StatsLookup_Typing Characters: $IRC_StatsLookup_Characters Words: $IRC_StatsLookup_Words Lines: $IRC_StatsLookup_Lines");
			
			// No results were found, means the nickname either joined before the bot was started or hasnt said anything. Which would allow for it to be in the nickname table but not in the stats one
			} else {
			
				IRC_Send($sock,"NOTICE $IRC_Nick :I recognise the nickname, but I don't have any statistics for it, please try another - !stats <nickname>");
			
			}
		
		}
		
		unset($IRC_mySQL_Temp);
	
	}

?>