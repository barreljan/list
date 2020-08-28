<?php
// HTML generation bits to keep the PHP code nice and tidy
// And shared functions between main view and admin portal

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if (isset($debugmode) && ($debugmode == "true")) {
	$start_time = microtime(true);
	error_reporting(-1);
	ini_set('display_errors', 'On');
}

function dbConnect() {
	global $conn, $db_HOST, $db_USER, $db_PASS, $db_NAME;

	if ($conn == false){
		$conn = mysqli_connect($db_HOST,$db_USER,$db_PASS,$db_NAME);
		if (mysqli_connect_error()) {
			exit();
		}
	}
}

dbConnect();

function setDefaults() {
	$_SESSION['show_list'] = '1';
	$_SESSION['buttonleft'] = 'gehaald';
	$_SESSION['item'] = 'nieuwe boodschap';
	$_SESSION['heading'] = "   <h2>Boodschappen</h2>\n   De te halen items zijn:\n   <br><br>";
}

function logger($msg) {
	global $conn, $logenable;

	if ($logenable == "true") {
		if ($conn == false){
			dbConnect();
		}
		if (isset($_SESSION['username']) && isset($_SESSION['clientip'])) {
			$authenticated = 1;
		} else {
			$authenticated = 0;
		}
		// To fill in a localhost address in favor of the email script
		if (!isset($_SESSION['clientip']) && (strpos($msg, "CRON_JOB"))) {
			$clientip = "127.0.0.1";
		} else {
			$clientip = $_SERVER['REMOTE_ADDR'];
	 	}
		$msg = mysqli_real_escape_string($conn, $msg);
		$logquery = "INSERT INTO `logs` (clientip,authenticated,message) VALUES (\"{$clientip}\",{$authenticated},\"$msg\")";
		$logresult = mysqli_query($conn,$logquery);
	}
}

function dispHtmlLoginportal() {
	$origin = htmlentities($_SERVER['SCRIPT_NAME']);
	dispHtmlHeader();
	echo " <div id=\"login\">";
	echo " <br><br>\n <form name=\"form1\" method=\"post\" action=\"{$origin}\">\n";
	echo "  <table class=\"lgouter\" >\n";
	echo "  <tr><td>\n";
	echo "\t<table class=\"lginner\" >\n";
	echo "\t\t<tr><td colspan=\"3\"><strong>Lijst Login </strong></td></tr>\n";
	echo "\t\t<tr><td class=\"lg1\">Username</td><td class=\"lg2\">:</td><td class=\"lg3\"><input name=\"myusername\" type=\"text\"></td></tr>\n";
	echo "\t\t<tr><td>Password</td><td>:</td><td><input name=\"mypassword\" type=\"password\"></td></tr>\n";
	echo "\t\t<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type=\"submit\" name=\"LOGIN\" value=\"Login\"><input type=\"hidden\" name=\"DOLOGIN\"></td></tr>\n";
	echo "\t</table>\n";
	echo "\t</td></tr>\n";
	echo "  </table>\n </form>";
	echo " </div>";
	dispHtmlfooter();
}

function session_register() {
	global $conn;

	session_regenerate_id();
	$currsession_id = session_id();
	$username = mysqli_real_escape_string($conn, $_SESSION['username']);
	$uagent = base64_encode(mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT']));
	$session_sql = "INSERT INTO `sessions` (clientip,username,sessionid,uagent,active) VALUES (\"{$_SESSION['clientip']}\",\"{$username}\",\"{$currsession_id}\",\"{$uagent}\",1)";
	$session_result = mysqli_query($conn,$session_sql) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
	logger("%INFORMAT% - User {$_SESSION['username']} logged in successfully ");
	return;
}

function session_logout() {
	global $conn;

	$currsession_id = session_id();
	$logout_sql = "UPDATE `sessions` SET `active` = 0, `endtime` = CURRENT_TIMESTAMP WHERE `sessionid` = \"{$currsession_id}\"";
	$logout_result = mysqli_query($conn,$logout_sql) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
	logger("%INFORMAT% - User {$_SESSION['username']} logged out");
	session_destroy();
	$_SESSION = array();

	dispHtmlHeader();
	echo "<br>\nUser logged out...";
	dispHtmlfooter();
	return;
}

function session_check() {
	global $conn;

	$oldsession_id = session_id();
	$username = $_SESSION['username'];
	$check_sql = "SELECT * FROM `sessions` WHERE `sessionid` = \"{$oldsession_id}\" AND `active` = 1 AND `username` = \"{$username}\"";
	$check_result = mysqli_query($conn,$check_sql) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
	if (mysqli_num_rows($check_result) == 1) {
		// First, check if session is not hijacked
		$sessionrow = mysqli_fetch_assoc($check_result);
		if ($sessionrow['clientip'] != $_SERVER['REMOTE_ADDR']) {
			if ($sessionrow['uagent'] != base64_encode($_SERVER['HTTP_USER_AGENT'])) {
				// Oi, different IP and different browser
				logger("%SECURITY% - Possible session hijacked for user {$sessionrow['username']}");
				dispHtmlErrorpage("SESS_HIJACK","");
				exit();
			}
			// Still here? Then it is a possible roaming user
			if (isset($_SERVER['REMOTE_ADDR'])) {
				logger("%INFORMAT% - User {$sessionrow['username']} is roaming ({$sessionrow['clientip']} vs {$_SERVER['REMOTE_ADDR']})");
			}
		}

		session_regenerate_id();
		$newsession_id = session_id();
		$session_sql = "UPDATE `sessions` SET `sessionid` = \"{$newsession_id}\" WHERE `sessionid` = \"{$oldsession_id}\"";
		$session_result = mysqli_query($conn,$session_sql) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));

		mysqli_free_result($check_result);
		return;
	} else {
		dispHtmlErrorpage("NO_SESSION","");
		mysqli_free_result($check_result);
		session_destroy();
		header("refresh:2;url=$page_SELF");
		exit();
	}
}

function session_handler() {
	global $conn, $db_TBLNAMEU, $page_SELF;

	if (isset($_GET['action'])) {
		if ($_GET['action'] == 'logout') {
			session_logout();
			header("refresh:2;url=$page_SELF");
			exit();
		}
	} 

	if (isset($_SESSION['username']) && isset($_SESSION['clientip']) && isset($_SESSION['groupid'])) {
		// User Authenticated
		session_check();
		return;
	} elseif (!empty($_GET)) {
		// Invalid input or someone trying to try RFI stuff
		header("HTTP/1.0 404 Not Found");
		exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested URL was not found on this server.</p>\n</body></html>\n");
	} elseif (isset($_POST['DOLOGIN'])) {
		$myusername = mysqli_real_escape_string($conn, $_POST['myusername']);
		$mypassword = mysqli_real_escape_string($conn, $_POST['mypassword']);
		$sql="SELECT * FROM `{$db_TBLNAMEU}` WHERE `username` = ('$myusername')";
		$result = mysqli_query($conn,$sql) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
		$userrow = mysqli_fetch_assoc($result);
		$password = $userrow['password'];
		$count = mysqli_num_rows($result);
		mysqli_free_result($result);
		// If result matched $myusername and $mypassword, table row must be 1 row and password be verified
		if(($count == 1) && (password_verify($mypassword, $password))){
			$_SESSION['username'] = $myusername;
			$_SESSION['groupid'] = $userrow['groupid'];
			$_SESSION['admin'] = $userrow['admin'];
			$_SESSION['clientip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT'];
			session_register();
			setDefaults();
			header("Location: $page_SELF");
			exit();
		} elseif ($count != 1) {
			logger("%AUTHFAIL% - Wrong Username: {$myusername}");
			dispHtmlErrorpage("NO_AUTH","");
			header("refresh:2;url=$page_SELF");
			exit();
		} elseif (($count = 1) && (password_verify($mypassword, $password) == false)) {
			logger("%AUTHFAIL% - Wrong Password for user: {$myusername}");
			dispHtmlErrorpage("NO_AUTH","");
			header("refresh:2;url=$page_SELF");
			exit();
		} else {
			logger("%AUTHFAIL% - Invalid login: {$myusername}");
			dispHtmlErrorpage("NO_AUTH","");
			header("refresh:2;url=$page_SELF");
			exit();
		}
	} else {
		dispHtmlLoginportal();
		exit();
	}
}

function dispHtmlHeader() {
	global $version;

	echo "<!DOCTYPE HTML>\n";
	echo "<html>\n <head>\n  <title>Dingen lijst</title>\n  <link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"./style.css\">";
	echo "\n  <meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\">";
	echo "\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\">";
	echo "\n  <meta name=\"copyright\" content=\"Copyright 2015-2017, bartjan@pc-mania.nl\">";
	echo "\n  <meta name=\"version\" content=\"v{$version}\">\n </head>\n<body>\n";
}

function dispHtmlfooter() {
	global $conn;

	if ($conn == true){
		mysqli_close($conn);
	}
	echo "\n </body>\n</html>";
}

function dispHtmlErrorpage($error,$issue) {

	dispHtmlHeader();
	if ($error == "NO_ITEM") {
		echo "   <br>\n   Lege invoer...\n   <br><br>\n   <a href=\".\">Ga terug</a>";
	} if ($error == "NO_ID") {
		echo "   <br>\n   Foei! geen ID\n   <br><br>\n   <a href=\".\">Ga terug</a>";
	} if ($error == "NO_DBCONN") {
		echo "   <br>\n   Failed to connect to MySQL<br><br>\nContact the webmaster<br>"; 
		if (isset($debugmode) && ($debugmode == "true")) {
			echo "\n$issue\n";
		}
	} if ($error == "QUERY_ERR") {
		echo "   <br>\n   Failed to query MySQL<br><br>\nContact the webmaster<br>";
		if (isset($debugmode) && ($debugmode == "true")) {
			echo "\n$issue";
		}
	} if ($error == "NO_RIGHTS") {
		echo "   <br>\n   Jij mag hier helaas niet zijn!\n   <br><br>\n   <a href=\".\">Ga terug</a>";
	} if ($error == "NO_AUTH") {
		echo "   <br>\n   Verkeerde Username of Password";
	} if ($error == "GROUP_NOTEMPTY") {
		echo "   <br>\n   Groep kan niet verwijderd worden, deze is niet leeg!";
	} if ($error == "SESS_HIJACK") {
		echo "   <br>\n   Dit is niet toegestaan!!";
		$subject = "Website violation op Lijst";
		$from = "no-reply@pc-mania.nl";
		$msg = "Er is een session violation op de website gedetecteerd\n\n";
		$msg .= "Violation time: ".date("Y-m-d H:i:s")."\n\n";
		$msg .= "Offender: {$_SERVER['REMOTE_ADDR']} \nUseragent: {$_SERVER['HTTP_USER_AGENT']} \nOrigin: {$_SERVER['PHP_SELF']}\n\n";
		$msg .= "Original session details:\n";
		foreach ($_SESSION as $key=>$val) {
		    $msg .= $key." = ".$val."\n";
		}
		$msg .= "\n\n\nTake actions asap!";
		$headers = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/plain; charset=iso-8859-1";
		$headers[] = "From: Uxx-001 webserver <{$from}>";
		$headers[] = "Reply-To: No-Reply <{$from}>";
		$headers[] = "X-Mailer: PHP/".phpversion();
		mail ("bartjan@h-p-c.nl",$subject,$msg,implode("\r\n", $headers),"-f {$from}");
	} if ($error == "SHORTPASS") {
		echo "   <br>\n   Password te kort, minimaal 8 karakters graag!";
	} if ($error == "NO_SESSION") {
		echo "   <br>\n   Oeps, je moet opnieuw inloggen, er is geen actieve sessie hier...";
	}
	dispHtmlfooter();
}
