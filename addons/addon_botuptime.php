<?php

	// Grab the time the bot started from the database
	$IRC_BotStart = IRC_Setting(BotStart);
	
	// Work out the duration since that time
	$diff = time() - $IRC_BotStart;
	$days = ($diff - ($diff % 86400)) / 86400;
	$diff = $diff - ($days * 86400);
	$hours = ($diff - ($diff % 3600)) / 3600;
	$diff = $diff - ($hours * 3600);
	$minutes = ($diff - ($diff % 60)) / 60;
	$diff = $diff - ($minutes * 60);
	$seconds = ($diff - ($diff % 1)) / 1; 
	$IRC_Bot_Uptime = "{$days}d {$hours}h {$minutes}m {$seconds}s";
	
	// Send to IRC
	IRC_Send($sock,"NOTICE $IRC_Nick :Uptime: $IRC_Bot_Uptime");

	// Show it in the console just because we can
	IRC_Console("Adn","Bot Uptime: $IRC_Bot_Uptime");

?>
