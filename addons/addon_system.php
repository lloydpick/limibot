<?php
	
	$exec = exec("uptime");
	IRC_Send($sock,"PRIVMSG $IRC_Channel :$exec");

?>
