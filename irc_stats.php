<?php

	// Transfer variable data from $IRC_Msg to $IRC_Msg_Temp so that original data isnt changed
	$IRC_Msg_Temp = $IRC_Msg;
	
	// Remove the whitespaces from the beginning and the end
	$IRC_Msg_Temp = trim($IRC_Msg_Temp);
	
	// Count the number of characters in the string > $IRC_Stats_Character_Total
	$IRC_Stats_Character_Total = strlen($IRC_Msg_Temp);
	
	// Explode the string into an array so count() will work
	$IRC_Stats_Words = explode(" ", $IRC_Msg_Temp);
	
	// Count the number of words in the string > $IRC_Stats_Words
	$IRC_Stats_Words = count($IRC_Stats_Words);
	
	// Work out a rough guide of how long it took the user to type the message > $IRC_Stats_Duration
	$IRC_Stats_Duration = $IRC_Stats_Words * 0.6;
	
	// Count the individual characters
	$IRC_Stats_Character_A = substr_count("$IRC_Msg", "a"); 
	$IRC_Stats_Character_B = substr_count("$IRC_Msg", "b"); 
	$IRC_Stats_Character_C = substr_count("$IRC_Msg", "c"); 
	$IRC_Stats_Character_D = substr_count("$IRC_Msg", "d"); 
	$IRC_Stats_Character_E = substr_count("$IRC_Msg", "e"); 
	$IRC_Stats_Character_F = substr_count("$IRC_Msg", "f"); 
	$IRC_Stats_Character_G = substr_count("$IRC_Msg", "g"); 
	$IRC_Stats_Character_H = substr_count("$IRC_Msg", "h"); 
	$IRC_Stats_Character_I = substr_count("$IRC_Msg", "i"); 
	$IRC_Stats_Character_J = substr_count("$IRC_Msg", "j"); 
	$IRC_Stats_Character_K = substr_count("$IRC_Msg", "k"); 
	$IRC_Stats_Character_L = substr_count("$IRC_Msg", "l"); 
	$IRC_Stats_Character_M = substr_count("$IRC_Msg", "m"); 
	$IRC_Stats_Character_N = substr_count("$IRC_Msg", "n"); 
	$IRC_Stats_Character_O = substr_count("$IRC_Msg", "o"); 
	$IRC_Stats_Character_P = substr_count("$IRC_Msg", "p"); 
	$IRC_Stats_Character_Q = substr_count("$IRC_Msg", "q"); 
	$IRC_Stats_Character_R = substr_count("$IRC_Msg", "r"); 
	$IRC_Stats_Character_S = substr_count("$IRC_Msg", "s"); 
	$IRC_Stats_Character_T = substr_count("$IRC_Msg", "t"); 
	$IRC_Stats_Character_U = substr_count("$IRC_Msg", "u"); 
	$IRC_Stats_Character_V = substr_count("$IRC_Msg", "v"); 
	$IRC_Stats_Character_W = substr_count("$IRC_Msg", "w"); 
	$IRC_Stats_Character_X = substr_count("$IRC_Msg", "x"); 
	$IRC_Stats_Character_Y = substr_count("$IRC_Msg", "y"); 
	$IRC_Stats_Character_Z = substr_count("$IRC_Msg", "z");
	
	// Count the individual numbers
	$IRC_Stats_Character_Number_0 = substr_count("$IRC_Msg", "0"); 
	$IRC_Stats_Character_Number_1 = substr_count("$IRC_Msg", "1"); 
	$IRC_Stats_Character_Number_2 = substr_count("$IRC_Msg", "2"); 
	$IRC_Stats_Character_Number_3 = substr_count("$IRC_Msg", "3"); 
	$IRC_Stats_Character_Number_4 = substr_count("$IRC_Msg", "4"); 
	$IRC_Stats_Character_Number_5 = substr_count("$IRC_Msg", "5"); 
	$IRC_Stats_Character_Number_6 = substr_count("$IRC_Msg", "6"); 
	$IRC_Stats_Character_Number_7 = substr_count("$IRC_Msg", "7"); 
	$IRC_Stats_Character_Number_8 = substr_count("$IRC_Msg", "8"); 
	$IRC_Stats_Character_Number_9 = substr_count("$IRC_Msg", "9"); 
	
	// Get the total number of numbers ;)
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_0;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_1;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_2;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_3;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_4;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_5;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_6;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_7;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_8;
	$IRC_Stats_Character_Number_Total = $IRC_Stats_Character_Number_Total + $IRC_Stats_Character_Number_9;
	
	// Check to see if the stats row is already in the database
	$IRC_Stats_mySQL_Query = "SELECT * FROM `statistics` WHERE `nick` = '$IRC_Msg_Nick' AND `channel` = '$IRC_Msg_Target'";
	$IRC_Stats_mySQL_Result = mysql_query($IRC_Stats_mySQL_Query);
	$IRC_Stats_mySQL_Number = mysql_num_rows($IRC_Stats_mySQL_Result);
	
	if ($IRC_Stats_mySQL_Number > 0) {
	
		// If its in the database we need to UPDATE the stats row
		$IRC_Stats_mySQL_Query = "UPDATE `statistics` SET `lastseen` = NOW(), `typing` = `typing`+$IRC_Stats_Duration, `characters` = `characters`+$IRC_Stats_Character_Total, `words` = `words`+$IRC_Stats_Words, `lines` = `lines`+1 WHERE `nick` = '$IRC_Msg_Nick' AND `channel` = '$IRC_Msg_Target'";
		$IRC_Stats_mySQL_Result = mysql_query($IRC_Stats_mySQL_Query);
		
	} else {
 
 		if ($IRC_Msg_Target != 0) {
		
 			// If its not in the database we need to INSERT a stats row
			$IRC_Stats_mySQL_Query = "INSERT INTO `statistics` (`nick`,`channel`,`lastseen`,`typing`,`characters`,`words`,`lines`) VALUES ('$IRC_Msg_Nick','$IRC_Msg_Target',NOW(),'$IRC_Stats_Duration','$IRC_Stats_Character_Total','$IRC_Stats_Words','1')";
			$IRC_Stats_mySQL_Result = mysql_query($IRC_Stats_mySQL_Query);
		
		}
		
	}

?>