<?php

	// Break apart the user host string to get ip/host out
	$IRC_Admin_Command = substr($IRC_Msg,0,-2);
	$IRC_Admin_Command = explode(" ",$IRC_Admin_Command);
	
	// Ask the database if the admin is already logged in..
	$IRC_Admin_mySQL_Query = "SELECT * FROM `admins_temp` WHERE `admin` = '$IRC_Msg_Nick'";
	$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
	$IRC_Admin_mySQL_Number = mysql_num_rows($IRC_Admin_mySQL_Result);
	
	// If the admin is already logged in
	if($IRC_Admin_mySQL_Number > 0) { 
	
		// If the admin needs to know a command
		if($IRC_Admin_Command[0] == "HELP") {
		
			// This is the main command name, its what we search the database for when we check the permissions
			$IRC_Command = "HELP";
			
			// Check the admin has permission to use this function
			$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
			// If the admin has permission
			if($IRC_Allowed == 1) {
			
				// Get the help command the admin wants help on
				$IRC_Help = array_slice($IRC_Admin_Command, 1);
					
				// Count the number of values left (ie. the command)
				$IRC_Help_Words = count($IRC_Help);
					
				// Set temporary value
				$IRC_Help_Temp = 0;
					
				// Run a while loop
				while ($IRC_Help_Words > $IRC_Help_Temp) {
					
					// Make a new variable containing the command
					$IRC_Help_Command = "$IRC_Help_Command $IRC_Help[$IRC_Help_Temp]";
						
					// Increase temporary value
					$IRC_Help_Temp++;
						
				}
			
				// Trim the variable so that it contains no white space
				$IRC_Help_Command = trim($IRC_Help_Command);
				
				// Select the command from the commands database
				$IRC_Help_mySQL_Query = "SELECT * FROM `admins_commands` WHERE `command` = '$IRC_Help_Command'";
				$IRC_Help_mySQL_Result = mysql_query($IRC_Help_mySQL_Query);
				$IRC_Help_mySQL_Number = mysql_num_rows($IRC_Help_mySQL_Result);
				
				// If the command exists this will be true
				if($IRC_Help_mySQL_Number == 1) {
				
					// Get the three entries of the command desc, usage, and an example of the usage
					$IRC_Help_Description = mysql_result($IRC_Help_mySQL_Result,0,"description");
					$IRC_Help_Usage = mysql_result($IRC_Help_mySQL_Result,0,"usage");
					$IRC_Help_Example = mysql_result($IRC_Help_mySQL_Result,0,"example");
					
					// Tell the admin the help information
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Command: $IRC_Help_Command");
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Description: $IRC_Help_Description");
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Usage: $IRC_Help_Usage");
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Example: $IRC_Help_Example");
				
				// This will be true if the command doesnt exist
				} else if($IRC_Help_mySQL_Number == 0) {
				
					// This will be true if the user just inputted 'HELP' or 'HELP ' to the bot
					if($IRC_Help_Command == NULL) {
						
						// Select all the commands from the command database
						$IRC_Help_mySQL_Query = "SELECT `command` FROM `admins_commands` ORDER BY `command` ASC";
						$IRC_Help_mySQL_Result = mysql_query($IRC_Help_mySQL_Query);
						$IRC_Help_mySQL_Number = mysql_num_rows($IRC_Help_mySQL_Result);
						$IRC_Help_mySQL_Temp = 0;
						
						while ($IRC_Help_mySQL_Number > $IRC_Help_mySQL_Temp) {
					
							// Get the commands trigger
							$IRC_Help_Command_Trigger = mysql_result($IRC_Help_mySQL_Result,$IRC_Help_mySQL_Temp,"command");
							
							// Make a new variable containing the command
							$IRC_Help_Commands = "$IRC_Help_Commands $IRC_Help_Command_Trigger,";
						
							// Increase temporary value
							$IRC_Help_mySQL_Temp++;
						
						}
						
						// Trim off the whitespace
						$IRC_Help_Commands = trim($IRC_Help_Commands);
						
						// Tell the admin all the commands
						IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Bot Commands:");
						IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :$IRC_Help_Commands");
						IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :For more help on a command: HELP [COMMAND NAME], Example: HELP ADMIN DEL");
					
					} else {
					
						// Tell the admin that it doesnt exist
						IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Error, the command '$IRC_Help_Command' does not exist");
						
					}
					
				}
			
			} 
		
		}
	
		// If the admin ever has the need to logout
		if($IRC_Admin_Command[0] == "LOGOUT") {
			
			// Write this to the admin log
			IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
			
			// Delete the entry from the temporary database	
			$IRC_Admin_mySQL_Query = "DELETE FROM `admins_temp` WHERE `admin` = '$IRC_Msg_Nick'";
			$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
	
			// Tell the admin that they are now logged out
			IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Logged Out, Bye!");
			
			// Print to the console that they are logged out
			IRC_Console("Admin","$IRC_Msg_Nick logged out");
			
		}
		
		// If the admin needs to restart the bot for whatever reason
		if($IRC_Admin_Command[0] == "RESTART") {
		
			// This is the main command name, its what we search the database for when we check the permissions
			$IRC_Command = "RESTART";
			
			// Check the admin has permission to use this function
			$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
		
			// If the admin has permission
			if($IRC_Allowed == 1) {
		
				// Tell the admin that the bot will now restart
				IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Restarting");
			
				// Empty the temporary admin table, just in case an admin quits while the bot is restarting
				$IRC_Admin_mySQL_Query = "TRUNCATE TABLE `admins_temp`";
				$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
	
				// Disconnect the bot
				IRC_Quit($sock,"Restart requested by $IRC_Msg_Nick");
				
			} 
		
		}
		
		// If the bot hasnt identified, or services were restarted while the bot was on
		if($IRC_Admin_Command[0] == "IDENTIFY") {
		
			// This is the main command name, its what we search the database for when we check the permissions
			$IRC_Command = "IDENTIFY";
			
			// Check the admin has permission to use this function
			$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
			// If the admin has permission
			if($IRC_Allowed == 1) {
			
				// Get the specific command to identify from the database
				$IRC_Admin_NickIdentify = IRC_Setting(NickIdentify);
			
				// Send it to the IRC server
				IRC_Send($sock,"$IRC_Admin_NickIdentify");
				
				// Tell the admin
				IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
				
				// Print to the console that the request was asked for
				IRC_Console("Admin","Identify request by $IRC_Msg_Nick");
				
				// Write this to the admin log
				IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
				
			}
			
		}
		
		// The 'ADMIN' command, lets us access admin access commands
		if($IRC_Admin_Command[0] == "ADMIN") {
		
			// CHANGE, used to change admin specific variables such as IP, Password and Login name
			if($IRC_Admin_Command[1] == "CHANGE") {
			
				// Allow the admin to change his/her password (ADMIN CHANGE PASSWORD OldPass NewPass
				if($IRC_Admin_Command[2] == "PASSWORD") {
				
					// This is the main command name, its what we search the database for when we check the permissions
					$IRC_Command = "ADMIN CHANGE PASSWORD";
			
					// Check the admin has permission to use this function
					$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
					// If the admin has permission
					if($IRC_Allowed == 1) {
					
						// Get the account the admin is currently using
						$IRC_Admin_mySQL_Query = "SELECT * FROM `admins_temp` WHERE `admin` = '$IRC_Msg_Nick'";
						$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
						$IRC_Admin_Account = mysql_result($IRC_Admin_mySQL_Result,0,"account");
						
						// Get the current password for the admins account
						$IRC_Admin_mySQL_Query = "SELECT * FROM `admins` WHERE `nick` = '$IRC_Admin_Account'";
						$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
						$IRC_Admin_Pass_Cur = mysql_result($IRC_Admin_mySQL_Result,0,"pass");
						
						// Get the current password from the admins input
						$IRC_Admin_Pass_Old = $IRC_Admin_Command[3];
						
						// Get the new password the admin wants his pass set to
						$IRC_Admin_Pass_New = $IRC_Admin_Command[4];
						
						// If the current pass and the old pass are exactly the same (case sensitive)
						if($IRC_Admin_Pass_Cur === $IRC_Admin_Pass_Old) {
					
							// Change the password
							$IRC_Admin_mySQL_Query = "UPDATE `admins` SET `pass` = '$IRC_Admin_Pass_New' WHERE `nick` = '$IRC_Admin_Account'";
							$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
					
							// Tell the admin that the password has been changed
							IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done.");
					
							// Print to the console that a password has changed
							IRC_Console("Admin","Password Change - Account '$IRC_Admin_Account', New Password '$IRC_Admin_Pass_New'");
						
							// Write this to the admin log
							IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
						
						}
					
					}
					
				}
				
				// Allow the admin to change his/her IP which the bot allows access from (ADMIN CHANGE HOST OldHost NewHost
				if($IRC_Admin_Command[2] == "HOST") {
				
					// This is the main command name, its what we search the database for when we check the permissions
					$IRC_Command = "ADMIN CHANGE HOST";
			
					// Check the admin has permission to use this function
					$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
					// If the admin has permission
					if($IRC_Allowed == 1) {
					
						// Get the account the admin is currently using
						$IRC_Admin_mySQL_Query = "SELECT * FROM `admins_temp` WHERE `admin` = '$IRC_Msg_Nick'";
						$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
						$IRC_Admin_Account = mysql_result($IRC_Admin_mySQL_Result,0,"account");
						
						// Get the current host for the admins account
						$IRC_Admin_mySQL_Query = "SELECT * FROM `admins` WHERE `nick` = '$IRC_Admin_Account'";
						$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
						$IRC_Admin_Host_Cur = mysql_result($IRC_Admin_mySQL_Result,0,"host");
						$IRC_Admin_Super = mysql_result($IRC_Admin_mySQL_Result,0,"super");
						
						// Get the current host from the admins input
						$IRC_Admin_Host_Old = $IRC_Admin_Command[3];
						
						// Get the new host the admin wants his host set to
						$IRC_Admin_Host_New = $IRC_Admin_Command[4];
						
						// If the current host and the old host match continue
						if($IRC_Admin_Host_Cur == $IRC_Admin_Host_Old) {
						
							// Change the host
							$IRC_Admin_mySQL_Query = "UPDATE `admins` SET `host` = '$IRC_Admin_Host_New' WHERE `nick` = '$IRC_Admin_Account'";
							$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
					
							// Tell the admin that the host has been changed
							IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done. If you cannot login due to these changes, contact a Super Admin or Login to the Web-Administration.");
					
							// Print to the console that a password has changed
							IRC_Console("Admin","Host Change - Account '$IRC_Admin_Account', New Host '$IRC_Admin_Host_New'");
						
							// Write this to the admin log
							IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
						
						}
					
					}
					
				}
				
				if($IRC_Admin_Command[2] == "EMAIL") {
				
					// This is the main command name, its what we search the database for when we check the permissions
					$IRC_Command = "ADMIN CHANGE EMAIL";
			
					// Check the admin has permission to use this function
					$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
					// If the admin has permission
					if($IRC_Allowed == 1) {
					
						// Get the account the admin is currently using
						$IRC_Admin_mySQL_Query = "SELECT * FROM `admins_temp` WHERE `admin` = '$IRC_Msg_Nick'";
						$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
						$IRC_Admin_Account = mysql_result($IRC_Admin_mySQL_Result,0,"account");
						
						// Is this admin a super admin?
						$IRC_Admin_mySQL_Query = "SELECT * FROM `admins` WHERE `nick` = '$IRC_Admin_Account'";
						$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
						$IRC_Admin_Super = mysql_result($IRC_Admin_mySQL_Result,0,"super");
						
						// Get the new address the admin wants his e-mail set to
						$IRC_Admin_Mail = $IRC_Admin_Command[3];
						
						// Check to see if the user has inputted a nickname after the e-mail (Super Admin Command)
						if($IRC_Admin_Command[4] != "") {
						
							// If the user is a super admin change the nickname
							if($IRC_Admin_Super == 1) {
							
								// Check to see if the admin account exists
								$IRC_Admin_mySQL_Query = "SELECT * FROM `admins` WHERE `nick` = '$IRC_Admin_Command[4]'";
								$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
								$IRC_Admin_mySQL_Number = mysql_num_rows($IRC_Admin_mySQL_Result);
								
								// Admin Account Exists, allow the change
								if($IRC_Admin_mySQL_Number > 0) {
								
									// The super admin is changing this account and not their own
									$IRC_Admin_Account = $IRC_Admin_Command[4];
									
									// Change the address
									$IRC_Admin_mySQL_Query = "UPDATE `admins` SET `email` = '$IRC_Admin_Mail' WHERE `nick` = '$IRC_Admin_Account'";
									$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
					
									// Tell the admin that the e-mail has been changed
									IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done. E-Mail Address Changed for '$IRC_Admin_Account' to '$IRC_Admin_Mail'");
							
									// Print to the console that a password has changed
									IRC_Console("Admin","E-Mail Change - Account '$IRC_Admin_Account', New Address '$IRC_Admin_Mail' - Changed by Super Admin");
					
									// Write this to the admin log
									IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
									
								} 
								
							} else {
							
								// Change the address
								$IRC_Admin_mySQL_Query = "UPDATE `admins` SET `email` = '$IRC_Admin_Mail' WHERE `nick` = '$IRC_Admin_Account'";
								$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
				
								// Tell the admin that the e-mail has been changed
								IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done. E-Mail Address Changed to '$IRC_Admin_Mail'");
								
								// Print to the console that a password has changed
								IRC_Console("Admin","E-Mail Change - Account '$IRC_Admin_Account', New Address '$IRC_Admin_Mail'");
						
								// Write this to the admin log
								IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
								
							}

						} 
						
						// Its a normal admin changing thier e-mail address
						if($IRC_Admin_Command[4] == "") {
						
							// Change the address
							$IRC_Admin_mySQL_Query = "UPDATE `admins` SET `email` = '$IRC_Admin_Mail' WHERE `nick` = '$IRC_Admin_Account'";
							$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
				
							// Tell the admin that the e-mail has been changed
							IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done. E-Mail Address Changed to '$IRC_Admin_Mail'");
							
							// Print to the console that a password has changed
							IRC_Console("Admin","E-Mail Change - Account '$IRC_Admin_Account', New Address '$IRC_Admin_Mail'");
					
							// Write this to the admin log
							IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
							
						}
						
					}
					
				}
				
			}
			
			
			// So we are going to add an admin for the bot (ADMIN ADD NICK PASS HOST)
			if($IRC_Admin_Command[1] == "ADD") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "ADMIN ADD";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
		
					$IRC_Admin_New_Nick = $IRC_Admin_Command[2];
					$IRC_Admin_New_Pass = $IRC_Admin_Command[3];
					$IRC_Admin_New_Host = $IRC_Admin_Command[4];
				
					$IRC_Admin_mySQL_Query = "INSERT INTO `admins` (`id`,`nick`,`pass`,`host`) VALUES ('', '$IRC_Admin_New_Nick', '$IRC_Admin_New_Pass', '$IRC_Admin_New_Host')";
					$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
					
					// Tell the admin that the admin has been added
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done. Use Web-Admin to Setup Permissions");
			
					// Print to the console that an admins been added
					IRC_Console("Admin","New Admin - $IRC_Admin_New_Nick, $IRC_Admin_New_Pass, $IRC_Admin_New_Host");
				
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
			
			// So we are going to remove an admin for the bot (ADMIN DEL NICK)
			if($IRC_Admin_Command[1] == "DEL") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "ADMIN DEL";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
		
					// Get the admin nick you want to remove
					$IRC_Admin_Del_Nick = $IRC_Admin_Command[2];
					
					// mySQL Query to remove the row from the database
					$IRC_Admin_mySQL_Query = "DELETE FROM `admins` WHERE `nick` = '$IRC_Admin_Del_Nick'";
					$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
					
					// Tell the admin that the admin has been removed
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
				
					// Print to the console that an admins been removed
					IRC_Console("Admin","Deleted Admin - $IRC_Admin_Del_Nick");
					
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
									
			}
			
			// So we are going to list the admins for the bot (ADMIN LIST)
			if($IRC_Admin_Command[1] == "LIST") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "ADMIN LIST";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {

					// Ask the database how many admins there are
					$IRC_Admin_mySQL_Query = "SELECT `nick`,`host`,`email`,`super` FROM `admins` ORDER BY `super` DESC,`nick` ASC";
					$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
					$IRC_Admin_mySQL_Number = mysql_num_rows($IRC_Admin_mySQL_Result);
					$IRC_Admin_mySQL_Temp = 0;
					
					// Describe the layout of the reply
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :SU - Login - IP Address - E-Mail Address");
					
					while ($IRC_Admin_mySQL_Number > $IRC_Admin_mySQL_Temp) {
					
						// Get the admin specific data
						$IRC_Admin_List_Nick = mysql_result($IRC_Admin_mySQL_Result,$IRC_Admin_mySQL_Temp,"nick");
						$IRC_Admin_List_Host = mysql_result($IRC_Admin_mySQL_Result,$IRC_Admin_mySQL_Temp,"host");
						$IRC_Admin_List_Mail = mysql_result($IRC_Admin_mySQL_Result,$IRC_Admin_mySQL_Temp,"email");
						$IRC_Admin_List_Supr = mysql_result($IRC_Admin_mySQL_Result,$IRC_Admin_mySQL_Temp,"super");
						
						// Sleep for a second so that we dont flood the bot off with lots of admins
						sleep(1);
					
						// See if a super admin is in this entry
						if($IRC_Admin_List_Supr == 1) {
						
							// Tell the admin
							IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Y - $IRC_Admin_List_Nick - $IRC_Admin_List_Host - $IRC_Admin_List_Mail");
							
						} else {
					
							// Tell the admin
							IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :N - $IRC_Admin_List_Nick - $IRC_Admin_List_Host - $IRC_Admin_List_Mail");
							
						}
					
						// Go back to the start of the while loop
						$IRC_Admin_mySQL_Temp++;
					
					}
					
					// Tell the admin were done
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
					
					// Print to the console about whats happening
					IRC_Console("Admin","Listing Admins to $IRC_Msg_Nick");
					
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
		
		}
		
		// The 'CHANNEL' command, lets us make changes to the channels
		if($IRC_Admin_Command[0] == "CHANNEL") {
		
			// So we are going to add a channel for the bot to join (CHANNEL ADD #NAME)
			if($IRC_Admin_Command[1] == "ADD") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "CHANNEL ADD";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
		
					// Get the channel name
					$IRC_Channel_Name = $IRC_Admin_Command[2];
					
					// mySQL Query to insert into the database
					$IRC_Channel_mySQL_Query = "INSERT INTO `channels` (`id`,`channel`) VALUES ('', '$IRC_Channel_Name')";
					$IRC_Channel_mySQL_Result = mysql_query($IRC_Channel_mySQL_Query);
					
					// Join the new channel
					IRC_Send($sock,"JOIN $IRC_Channel_Name");
				
					// Tell the admin that the channel has been added
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
				
					// Print to the console that a channel has been added
					IRC_Console("Admin","New Channel - $IRC_Channel_Name");
					IRC_Console("Svr","Joining $IRC_Channel_Name");
					
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
			
			// So we are going to remove a channel from the bot (CHANNEL DEL #NAME)
			if($IRC_Admin_Command[1] == "DEL") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "CHANNEL DEL";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
		
					// Get the channel name
					$IRC_Channel_Name = $IRC_Admin_Command[2];
					
					// mySQL Query to delete the row from the database
					$IRC_Channel_mySQL_Query = "DELETE FROM `channels` WHERE `channel` = '$IRC_Channel_Name'";
					$IRC_Channel_mySQL_Result = mysql_query($IRC_Channel_mySQL_Query);
				
					// Join the new channel
					IRC_Send($sock,"PART $IRC_Channel_Name");
					
					// Tell the admin that the channel has been removed
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
				
					// Print to the console that a channels been removed
					IRC_Console("Admin","Deleted Channel - $IRC_Channel_Name");
					IRC_Console("Svr","Parting $IRC_Channel_Name");
					
					// Get the number of channels
					$IRC_Channel_mySQL_Query = "SELECT * FROM `channels`";
					$IRC_Channel_mySQL_Result = mysql_query($IRC_Channel_mySQL_Query);
					$IRC_Channel_mySQL_Number = mysql_num_rows($IRC_Channel_mySQL_Result);
					$IRC_Channel_mySQL_Number++;
					
					// Reset the auto_increment number to the correct number
					$IRC_Channel_mySQL_Query = "ALTER TABLE `channels` PACK_KEYS = 0 CHECKSUM = 0 DELAY_KEY_WRITE = 0 AUTO_INCREMENT = $IRC_Channel_mySQL_Number";
					$IRC_Channel_mySQL_Result = mysql_query($IRC_Channel_mySQL_Query);
				
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
			
			// So we are going to list the channels for the bot (CHANNEL LIST)
			if($IRC_Admin_Command[1] == "LIST") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "CHANNEL LIST";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {

					// Ask the database how many channels there are
					$IRC_Channel_mySQL_Query = "SELECT * FROM `channels` ORDER BY `channel` ASC";
					$IRC_Channel_mySQL_Result = mysql_query($IRC_Channel_mySQL_Query);
					$IRC_Channel_mySQL_Number = mysql_num_rows($IRC_Channel_mySQL_Result);
					$IRC_Channel_mySQL_Temp = 0;
					
					while ($IRC_Channel_mySQL_Number > $IRC_Channel_mySQL_Temp) {
					
						// Get the channel name
						$IRC_Channel_List = mysql_result($IRC_Channel_mySQL_Result,$IRC_Channel_mySQL_Temp,"channel");
						
						// Sleep for a second so that we dont flood the bot off with 20+ channels
						sleep(1);
						
						// Tell the admin
						IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :$IRC_Channel_List");
						
						// Go back to the start of the while loop
						$IRC_Channel_mySQL_Temp++;
					
					}
					
					// Tell the admin were done
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
					
					// Print to the console about whats happening
					IRC_Console("Admin","Listing Channels to $IRC_Msg_Nick");
					
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
				
				}
				
			}
		
		}
		
		// The 'MODE' command, lets us make mode changes to people and channels
		if($IRC_Admin_Command[0] == "MODE") {
		
			// Op someone (MODE OP #NAME NICK)
			if($IRC_Admin_Command[1] == "OP") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "MODE OP";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
			
					// Extract the nickname and channel
					$IRC_Mode_Channel = $IRC_Admin_Command[2];
					$IRC_Mode_Nick = $IRC_Admin_Command[3];
					
					// Op the person
					IRC_Send($sock,"MODE $IRC_Mode_Channel +o $IRC_Mode_Nick");
					
					// Tell the admin its been done
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
					
					// Print to the console about whats happening
					IRC_Console("Admin","Mode +o $IRC_Admin_Command[2] $IRC_Admin_Command[3] requested by $IRC_Msg_Nick");
					
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
		
				}
				
			}
			
			// De-Op someone (MODE DEOP #NAME NICK)
			if($IRC_Admin_Command[1] == "DEOP") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "MODE DEOP";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
			
					// Extract the nickname and channel
					$IRC_Mode_Channel = $IRC_Admin_Command[2];
					$IRC_Mode_Nick = $IRC_Admin_Command[3];
					
					// De-Op the person
					IRC_Send($sock,"MODE $IRC_Mode_Channel -o $IRC_Mode_Nick");
					
					// Tell the admin its been done
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
					
					// Print to the console about whats happening
					IRC_Console("Admin","Mode -o $IRC_Admin_Command[2] $IRC_Admin_Command[3] requested by $IRC_Msg_Nick");
				
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
			
			// Voice someone (MODE VOICE #NAME NICK)
			if($IRC_Admin_Command[1] == "VOICE") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "MODE VOICE";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
			
					// Extract the nickname and channel
					$IRC_Mode_Channel = $IRC_Admin_Command[2];
					$IRC_Mode_Nick = $IRC_Admin_Command[3];
					
					// De-Op the person
					IRC_Send($sock,"MODE $IRC_Mode_Channel +v $IRC_Mode_Nick");
					
					// Tell the admin its been done
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
					
					// Print to the console about whats happening
					IRC_Console("Admin","Mode +v $IRC_Admin_Command[2] $IRC_Admin_Command[3] requested by $IRC_Msg_Nick");
					
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
			
			// De-Voice someone (MODE DEVOICE #NAME NICK)
			if($IRC_Admin_Command[1] == "DEVOICE") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "MODE DEVOICE";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
			
					// Extract the nickname and channel
					$IRC_Mode_Channel = $IRC_Admin_Command[2];
					$IRC_Mode_Nick = $IRC_Admin_Command[3];
					
					// De-Op the person
					IRC_Send($sock,"MODE $IRC_Mode_Channel -v $IRC_Mode_Nick");
					
					// Tell the admin its been done
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
				
						// Print to the console about whats happening
					IRC_Console("Admin","Mode -v $IRC_Admin_Command[2] $IRC_Admin_Command[3] requested by $IRC_Msg_Nick");
					
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
			
			// De-Voice someone (MODE KICK #NAME NICK)
			if($IRC_Admin_Command[1] == "KICK") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "MODE KICK";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
			
					// Get the channel the admin wants the kick performed on
					$IRC_Kick_Channel = $IRC_Admin_Command[2];
				
					// Get the nickname the admin wants kicked
					$IRC_Kick_Nick = $IRC_Admin_Command[3];
				
					// Send the IRC code to the server
					IRC_Send($sock,"KICK $IRC_Kick_Channel $IRC_Kick_Nick :Kicked by $IRC_Msg_Nick");
				
					// Tell the admin its been done
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
				
					// Print to the console about whats happening
					IRC_Console("Admin","Kick $IRC_Kick_Nick out of $IRC_Kick_Channel requested by $IRC_Msg_Nick");
				
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
				
		}
		
		// The 'SET' command, lets us set modes for channels and allows us to set topics
		if($IRC_Admin_Command[0] == "SET") {
		
			// Change the topic (SET TOPIC #NAME TOPIC)
			if($IRC_Admin_Command[1] == "TOPIC") {
			
				// This is the main command name, its what we search the database for when we check the permissions
				$IRC_Command = "SET TOPIC";
			
				// Check the admin has permission to use this function
				$IRC_Allowed = IRC_Admin_Allowed("$IRC_Command","$IRC_Msg_Nick","$IRC_Msg",$sock);
			
				// If the admin has permission
				if($IRC_Allowed == 1) {
		
					// Get the channel the admin wants the topic changed on
					$IRC_Topic_Channel = $IRC_Admin_Command[2];
				
					// Get the new topic, first slice the array from 3 values in
					$IRC_Topic_Topic = array_slice($IRC_Admin_Command, 3);
					
					// Count the number of values left (ie. the topic)
					$IRC_Topic_Words = count($IRC_Topic_Topic);
					
					// Set temporary value
					$IRC_Topic_Temp = 0;
					
					// Run a while loop
					while ($IRC_Topic_Words > $IRC_Topic_Temp) {
					
						// Make a new variable containing the topic
						$IRC_Topic = "$IRC_Topic $IRC_Topic_Topic[$IRC_Topic_Temp]";
						
						// Increase temporary value
						$IRC_Topic_Temp++;
						
					}
					
					// Trim the topic to remove any white space
					$IRC_Topic = trim($IRC_Topic);

					// Send the IRC code to the server
					IRC_Send($sock,"TOPIC $IRC_Topic_Channel :$IRC_Topic");
				
					// Tell the admin its been done
					IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Done");
				
					// Print to the console about whats happening
					IRC_Console("Admin","Topic Change - $IRC_Topic_Channel - $IRC_Topic_Topic");
				
					// Write this to the admin log
					IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
					
				}
				
			}
						
		}
		
	}
	
	// Is the first word "LOGIN"?
	if($IRC_Admin_Command[0] == "LOGIN") {
		
		// An admin is trying to login, ask the database if this is a true admin
		$IRC_Admin_mySQL_Query = "SELECT * FROM `admins` WHERE `nick` = '$IRC_Admin_Command[1]' AND `pass` = '$IRC_Admin_Command[2]' AND `host` = '$IRC_Msg_Host'";
		$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
		$IRC_Admin_mySQL_Number = mysql_num_rows($IRC_Admin_mySQL_Result);
		
		// If the admin logged in correctly..
		if ($IRC_Admin_mySQL_Number > 0) {
		
			// Check they arnt already logged in..
			$IRC_Admin_mySQL_Query = "SELECT * FROM `admins_temp` WHERE `admin` = '$IRC_Admin_Command[1]'";
			$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
			$IRC_Admin_mySQL_Number = mysql_num_rows($IRC_Admin_mySQL_Result);
			
			// If the admins name was found in the temporary table..
			if($IRC_Admin_mySQL_Number > 0) {
				
				// Tell the admin they are already logged in
				IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Already Logged In on Account $IRC_Admin_Command[1], LOGOUT first.");
				
				// Print to the console that an errors occured
				IRC_Console("Admin","Error \t$IRC_Msg_Nick already logged in under account $IRC_Admin_Command[1]");
				
			} else {
		
				// Insert his/her nickname into the temporary admin table so that they dont have to login again unless they quit
				$IRC_Admin_mySQL_Query = "INSERT INTO `admins_temp` (`id`,`admin`,`account`,`time`) VALUES ('', '$IRC_Msg_Nick','$IRC_Admin_Command[1]', NOW( ) )";
				$IRC_Admin_mySQL_Result = mysql_query($IRC_Admin_mySQL_Query);
			
				// Print to the console that an admin has logged in
				IRC_Console("Admin","$IRC_Msg_Nick Logged In on Account $IRC_Admin_Command[1]");
				
				// Tell the admin they are logged in
				IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Access Granted");
				
				// Write this to the admin log
				IRC_Admin_Log("$IRC_Msg_Nick","$IRC_Msg");
				
			}
		
		} else {
		
			// Print to the console that someone tried to login
			IRC_Console("Admin","Access Denied to Account '$IRC_Admin_Command[1]' for nickname '$IRC_Msg_Nick - $IRC_Msg_Host'");
				
			// Tell the person that they dont have access
			IRC_Send($sock,"PRIVMSG $IRC_Msg_Nick :Access Denied");
		
		}
		
	} 
	
?>