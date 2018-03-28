<?php
include '/var/www/html/lijst/config.php';
include '/var/www/html/lijst/functions.php';
__dbConnect__();


// Garbage cleaner for stale sessions (30min idle)
	$sql = "SELECT `id` FROM `sessions` WHERE `active` = 1 AND `starttime` < (NOW() - INTERVAL 30 MINUTE)";
	$stalesessions = mysqli_query($conn,$sql) or die;

	$i = 0;
	$numrows = mysqli_num_rows($stalesessions);
	if ($numrows != 0) {
		$ids = "";
		while ($row = mysqli_fetch_assoc($stalesessions)) {
			$ids .= "{$row['id']}";
			if ($numrows > 1 && $i != ($numrows - 1)) {
				$ids .= ",";
			}
			$i++;
		}
		$updatesql = "UPDATE `sessions` SET `active` = 0 WHERE `starttime` < (NOW() - INTERVAL 30 MINUTE)";
		$update = mysqli_query($conn,$updatesql) or die;
		__logger__("%CRON_JOB% - Stale sessions cleaned for ID(s) {$ids}");
	}
	mysqli_free_result($stalesessions);


// Send update through email with new items
	$query = "SELECT `{$db_TBLNAMEG}`.`groupid`, 
		  GROUP_CONCAT(`{$db_TBLNAMEU}`.`emailadres`) as `recepients` FROM `{$db_TBLNAMEG}` 
		  INNER JOIN `{$db_TBLNAMEU}` ON `{$db_TBLNAMEU}`.`groupid` = `{$db_TBLNAMEG}`.`groupid` 
		  GROUP BY `{$db_TBLNAMEG}`.`groupid`;";
	$email_recepients = mysqli_query($conn, $query) or die ("query error");

	while ($receprow = mysqli_fetch_assoc($email_recepients)){
		$query = "SELECT `item` FROM {$db_TBLNAME} WHERE `emailout` = 0 AND `ordered` = 0 AND `active` = 1 AND `list` = 1 AND `groupid` = {$receprow['groupid']}";
		$email_items = mysqli_query($conn, $query) or die ("query error");
		if (mysqli_num_rows($email_items) != 0){
			$msg = "Er zijn nieuwe boodschappen ingevoerd:\n\n";
			while ($row = mysqli_fetch_assoc($email_items)){
				$msg .= "- ".$row['item']."\n";
			}
			$msg .= "\n\nKijk ook op {$page_SELF}\n";
			$msg = wordwrap($msg,70);
			$subject = "Boodschappenlijst update";
			$from = "no-reply@pc-mania.nl";
			$headers = array();
			  $headers[] = "MIME-Version: 1.0";
			  $headers[] = "Content-type: text/plain; charset=iso-8859-1";
			  $headers[] = "From: Boodschappenlijst <{$from}>";
			  $headers[] = "Reply-To: No-Reply <{$from}>";
			  $headers[] = "X-Mailer: PHP/".phpversion();

			mail($receprow['recepients'],$subject,$msg,implode("\r\n", $headers),"-f".$from);

			// Update the new items that an email has been sent
			$query = "UPDATE {$db_TBLNAME} SET `emailout` = 1 WHERE `emailout` = 0";
			mysqli_query($conn, $query) or die ("query error");
			__logger__("%CRON_JOB% - Update sent to email for group {$receprow['groupid']} ({$receprow['recepients']})");
		}
	}
	mysqli_free_result($email_recepients);
	mysqli_free_result($email_items);



mysqli_close($conn);
?>
