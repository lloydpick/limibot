<?php
	
	if ($IRC_Channel_ID == 0) {
	
		// Seems we have a pe0n of an IRC user who thinks pm'ing the bot with no channel is clever
	
	} else {
	
		// Lets see what nickname they want seen info about
		$IRC_Seen_Nick = $IRC_Addons_Temp_2[1];
		
		// Did they actually input a nickname? if not...
		if($IRC_Seen_Nick == "") {
			
			IRC_Send($sock,"NOTICE $IRC_Nick :Please specify a nickname - !seen <nickname>");
		
		// If they did input a nickname...
		} else {
		
			// Lets grab the requested nicknames id's from the nicks table
			$IRC_Seen_1 = "SELECT * FROM `nicks` WHERE `nick` = '$IRC_Seen_Nick'";
			$IRC_Seen_Result_1 = mysql_query($IRC_Seen_1);
			$IRC_Seen_Temp = mysql_num_rows($IRC_Seen_Result_1);
		
			// Did that exist?
			if ($IRC_Seen_Temp > 0) {
			
				// If it did, get the id number
				$IRC_Seen_ID = mysql_result($IRC_Seen_Result_1,0,"id");
				$IRC_Seen_NickID = $IRC_Seen_ID;
				
			}
			
			// Did it bring back an ID? If it did'nt...		
			if ($IRC_Seen_NickID == "") {
	
				IRC_Send($sock,"NOTICE $IRC_Nick :I don't recognise that nickname, please try another - !seen <nickname>");
	
			// If it did bring back an ID number...
			} else {
			
				// Now lets go and grab the stats information from the statistics table based on nickname id and the channel id it was requested from
				$IRC_mySQL_Query_1 = "SELECT * FROM `statistics` WHERE `nick` = '$IRC_Seen_NickID' AND `channel` = '$IRC_Channel_ID'";
				$IRC_mySQL_Result_1 = mysql_query($IRC_mySQL_Query_1);
				$IRC_mySQL_Temp = mysql_num_rows($IRC_mySQL_Result_1);
				
				// Any results? If so...
				if ($IRC_mySQL_Temp > 0) {
		
					$IRC_Seen_LastSeen = mysql_result($IRC_mySQL_Result_1,0,"lastseen");
					
					// This will go get what the user last did, no error checking needed as it wont get this far if the nickname didnt exist
					$IRC_mySQL_Query_2 = "SELECT * FROM `logs` WHERE `nick` = '$IRC_Seen_NickID' AND `channel` = '$IRC_Channel_ID' ORDER BY `id` DESC LIMIT 0,1";
					$IRC_mySQL_Result_2 = mysql_query($IRC_mySQL_Query_2);
					$IRC_Seen_Time = mysql_result($IRC_mySQL_Result_2,0,"time");
					$IRC_Seen_LastAction = mysql_result($IRC_mySQL_Result_2,0,"type");
					$IRC_Seen_Log = mysql_result($IRC_mySQL_Result_2,0,"log");
					$IRC_Seen_Log = substr($IRC_Seen_Log,0,-2);
					
					$IRC_Seen_Time_Year=substr($IRC_Seen_Time,0,4);
					$IRC_Seen_Time_Month=substr($IRC_Seen_Time,4,2);
					$IRC_Seen_Time_Day=substr($IRC_Seen_Time,6,2);
					$IRC_Seen_Time_Hour=substr($IRC_Seen_Time,8,2);
					$IRC_Seen_Time_Minute=substr($IRC_Seen_Time,10,2);
					$IRC_Seen_Time_Second=substr($IRC_Seen_Time,12,2);
					
					$IRC_Seen_Time = "$IRC_Seen_Time_Day/$IRC_Seen_Time_Month/$IRC_Seen_Time_Year - $IRC_Seen_Time_Hour:$IRC_Seen_Time_Minute:$IRC_Seen_Time_Second";
					
					// Send the info back to the people who requested it in notice from
					IRC_Send($sock,"NOTICE $IRC_Nick :Seen Information for '$IRC_Seen_Nick'");
	
					if($IRC_Seen_LastAction == "Join") {
	
						IRC_Send($sock,"NOTICE $IRC_Nick :Last Seen at $IRC_Seen_Time - $IRC_Seen_LastAction"."ed $IRC_Channel");
	
					} else if($IRC_Seen_LastAction == "Part") {
	
						IRC_Send($sock,"NOTICE $IRC_Nick :Last Seen at $IRC_Seen_Time - $IRC_Seen_LastAction"."ed $IRC_Channel");
	
					} else {
						
						IRC_Send($sock,"NOTICE $IRC_Nick :Last Seen at $IRC_Seen_Time - $IRC_Seen_LastAction - \"$IRC_Seen_Log\"");
						
					}
				
				// No results were found, means the nickname either joined before the bot was started or hasnt said anything. Which would allow for it to be in the nickname table but not in the stats one
				} else {
				
					IRC_Send($sock,"NOTICE $IRC_Nick :I recognise the nickname, but I don't have any seen information for it, please try another - !seen <nickname>");
					
				}
			
			}
			
			unset($IRC_mySQL_Temp);
		
		}
		
	}

?>