<?php
// Alle dingen lijst v0.97 --- Admin portal
// Credits: bartjan@pc-mania.nl

include './config.php';
include './functions.php';

session_handler();

if ($_SESSION['admin'] != 1){
	dispHtmlErrorpage("NO_RIGHTS","");
	exit();
}

// Build of mainpage from here
dispHtmlHeader();

// Add new item to DB
if (isset($_POST['ADDUSER'])) {
	if ($_POST['iUSER'] == "" || $_POST['iUSER'] == NULL || $_SESSION['username'] == "" || $_POST['iEMAIL'] == "" || $_POST['iEMAIL'] == NULL || $_POST['iGROUP'] == 0) {
		dispHtmlErrorpage("NO_ITEM","");
		exit();
	} else {
		$user = mysqli_real_escape_string($conn, $_POST['iUSER']); 
		$email = mysqli_real_escape_string($conn, $_POST['iEMAIL']);
		$query = "INSERT INTO `{$db_TBLNAMEU}` (username,emailadres,groupid,active) VALUES (\"{$user}\",\"{$email}\",\"{$_POST['iGROUP']}\",\"1\")";
		mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
		header("Location: $page_ADMIN");
	}
}
if (isset($_POST['CHANGEUSER'])) {
	if ($_POST['iUSER'] == "" || $_POST['iUSER'] == NULL || $_SESSION['username'] == "" || $_POST['iEMAIL'] == "" || $_POST['iEMAIL'] == NULL || $_POST['iGROUP'] == 0) {
		dispHtmlErrorpage("NO_ITEM","");
		exit();
	} else {
		$user = mysqli_real_escape_string($conn, $_POST['iUSER']); 
		$email = mysqli_real_escape_string($conn, $_POST['iEMAIL']);
		$password = mysqli_real_escape_string($conn, $_POST['iPASS']);
		if (strlen($password) < 7 || strlen($password) == 7) {
			dispHtmlErrorpage("SHORTPASS","");
			exit();
		}
		$options = ['cost' => 12];
		$encrpass = password_hash($password, PASSWORD_BCRYPT, $options);
		if ($password == "blankpassword") {
			$query = "UPDATE `{$db_TBLNAMEU}` SET `username` = \"{$_POST['iUSER']}\",`emailadres` = \"{$_POST['iEMAIL']}\",`groupid` = \"{$_POST['iGROUP']}\" WHERE `id` = {$_POST['USRID']}";
		} else {
			$query = "UPDATE `{$db_TBLNAMEU}` SET `username` = \"{$_POST['iUSER']}\",`password` = \"{$encrpass}\",`emailadres` = \"{$_POST['iEMAIL']}\",`groupid` = \"{$_POST['iGROUP']}\" WHERE `id` = {$_POST['USRID']}";
		}
		mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
		header("Location: $page_ADMIN");
	}
}
if (isset($_POST['CHANGEGROUP'])) {
	if ($_POST['iGRP'] == "" || $_POST['iGRP'] == NULL || $_SESSION['username'] == "") {
		dispHtmlErrorpage("NO_ITEM","");
		exit();
	} else {
		$user = mysqli_real_escape_string($conn, $_POST['iGRP']); 
		$query = "UPDATE `{$db_TBLNAMEG}` SET `groupname` = \"{$_POST['iGRP']}\" WHERE `groupid` = \"{$_POST['GRPID']}\"";
		echo $query;
		mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
		header("Location: $page_ADMIN");
	}
}
if (isset($_POST['ADDGROUP'])) {
	if ($_POST['iGROUP'] == "" || $_POST['iGROUP'] == NULL || $_SESSION['username'] == "") {
		dispHtmlErrorpage("NO_ITEM","");
		exit();
	} else {
		$group = mysqli_real_escape_string($conn, $_POST['iGROUP']); 
		$query = "INSERT INTO `{$db_TBLNAMEG}` (groupname,active) VALUES (\"{$group}\",\"1\")";
		mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
		header("Location: $page_ADMIN");
	}
}
 
// Modify or delete item
if (isset($_GET['action'])) {
	if (empty($_GET['id'])) {
		dispHtmlErrorpage("NO_ID","");
		exit();
	} else {
		$id=$_GET['id'];
		if ($_GET['action'] == 'deleteusr') {
			$query = "UPDATE `{$db_TBLNAMEU}` SET `active` = '0' WHERE `id` = {$id}";
			mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
			header("Location: $page_ADMIN");
		}
		if ($_GET['action'] == 'deletegrp') {
			$query = "SELECT * from `{$db_TBLNAMEU}` WHERE `groupid` = {$id}";
			$grp_result = mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
			if (!mysqli_num_rows($grp_result) == 0) {
				dispHtmlErrorpage("GROUP_NOTEMPTY","");
				exit();
			} elseif (mysqli_num_rows($grp_result) == 0) {
				$query = "UPDATE `{$db_TBLNAMEG}` SET `active` = '0' WHERE `groupid` = {$id}";
				mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
				header("Location: $page_ADMIN");
			}
		}
		if (($_GET['action'] == 'changeusr')) {
			$query = "SELECT id,username,password,emailadres,groupid FROM `{$db_TBLNAMEU}` WHERE `id` = {$id}";
			$usr_result = mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
			if (mysqli_num_rows($usr_result) == 0) {
				header("Location: $page_ADMIN");
			}
			$userrow = mysqli_fetch_assoc($usr_result);
			$inputfldusr = "<input type=\"text\" name=\"iUSER\" length=\"100\" size=\"53\" value=\"{$userrow['username']}\"><br>\n";
			$inputfldemail = "<input type=\"text\" name=\"iEMAIL\" length=\"100\" size=\"53\" value=\"{$userrow['emailadres']}\"><br>\n";
			if ($userrow['password'] == "") {
				$inputfldpass = "<input type=\"password\" name=\"iPASS\" length=\"100\" size=\"53\" value=\"\"><br>\n";
			} elseif (strlen($userrow['emailadres']) > 2 ) {
				$inputfldpass = "<input type=\"password\" name=\"iPASS\" length=\"100\" size=\"53\" value=\"blankpassword\"><br>\n";
			}
 		}
		if ($_GET['action'] == 'changegrp') {
			$query = "SELECT groupid,groupname from `{$db_TBLNAMEG}` WHERE `groupid` = {$id}";
			$grp_result = mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
			if (mysqli_num_rows($grp_result) == 0) {
				header("Location: $page_ADMIN");
			}
			$grouprow = mysqli_fetch_assoc($grp_result);
			$inputfldgrp = "<input type=\"text\" name=\"iGRP\" length=\"100\" size=\"53\" value=\"{$grouprow['groupname']}\"><br>\n";
		}
	}
}

echo "<div id=\"page\">\n";
echo "<h4><a href=\"{$page_SELF}\" class=header>home</a> | <a href=\"{$page_SELF}logviewer.php\" class=header>logs</a> | admin | <a href=\"{$page_SELF}index.php?action=logout\" class=header>logout</a></h4>\n";
echo "<h2>Admin page</h2>\n";

// Get currently active items from DB
echo "Users\n<br>\n<br>\n";
echo "\n   <table class=\"admin\">";
echo "\n   <tr><th>Name</th><th>Emailadres</th><th>Group</th><th></th><th></th></tr>\n";

// Get Users
$query = "SELECT * FROM `users` WHERE `active` = \"1\" "; //ORDER BY `groupid`";
$items_result = mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));

while ($usrsrow = mysqli_fetch_assoc($items_result)){
	echo "\n   <tr>\n     <td class=\"admin\" width=\"200px\">{$usrsrow["username"]}</td>\n";
	echo "     <td class=\"admin\" width=\"200px\">{$usrsrow["emailadres"]}</td>\n"; 
	echo "     <td class=\"admin\" width=\"200px\">{$usrsrow["groupid"]}</td>\n";
	echo "     <td class=\"admin\"><a href=\"{$page_ADMIN}?action=changeusr&amp;id={$usrsrow["id"]}\"><span class=\"ink-label gray\">Wijzig</span></a></td>\n";
	echo "     <td class=\"admin\"><a href=\"{$page_ADMIN}?action=deleteusr&amp;id={$usrsrow["id"]}\"><span class=\"ink-label gray\">Verwijder</span></a></td>";
	echo "\n   </tr>";
}
mysqli_free_result($items_result);

echo "</table>\n<br>\n<br>\nGroups\n<br>\n<br>\n";
echo "<table class=\"admin\">";
echo "\n   <tr><th>Groupname</th><th>Group</th><th></th><th></th></tr>\n";

// Get Groups
$query = "SELECT * FROM `groups` WHERE `active` = \"1\" ORDER BY `groupid`";
$items_result = mysqli_query($conn,$query) or die (dispHtmlErrorpage("QUERY_ERR",mysqli_error($conn)));
$grouplist = "<select name=\"iGROUP\"><option value=\"0\">Selecteer groep</option>\n";
while ($grprow = mysqli_fetch_assoc($items_result)){
	echo "\n   <tr>\n     <td class=\"admin\" width=\"200px\">{$grprow["groupname"]}</td>\n";
	echo "     <td class=\"admin\" width=\"200px\">{$grprow["groupid"]}</td>\n";
	echo "     <td class=\"admin\"><a href=\"{$page_ADMIN}?action=changegrp&amp;id={$grprow["groupid"]}\"><span class=\"ink-label gray\">Wijzig</span></a></td>\n";
	echo "     <td class=\"admin\"><a href=\"{$page_ADMIN}?action=deletegrp&amp;id={$grprow["groupid"]}\"><span class=\"ink-label gray\">Verwijder</span></a></td>";
	echo "\n   </tr>";
	if (isset($_GET['action'])) {
		if (($_GET['action'] == 'changeusr') && ($userrow['groupid'] == $grprow["groupid"])) {
			$grouplist .= "\t<option value=\"{$grprow["groupid"]}\" selected>{$grprow["groupname"]}</option>\n";
		} else {
			$grouplist .= "\t<option value=\"{$grprow["groupid"]}\">{$grprow["groupname"]}</option>\n";
		}
	}
	if (!isset($_GET['action'])) {
		$grouplist .= "\t<option value=\"{$grprow["groupid"]}\">{$grprow["groupname"]}</option>\n";
	}
}	
mysqli_free_result($items_result);
echo "\n   </table>";

// Make User form
echo "\n   <form action=\"{$page_ADMIN}\" method=\"post\" name=\"USER\">";
if (!isset($inputfldusr) || $inputfldusr == "" || $inputfldusr == NULL ) {
	echo "\n     <br><br><br>\n\t Voer een nieuwe user in:<br>";
  	echo "\n\t<input type=\"text\" name=\"iUSER\" length=\"100\" size=\"53\">\n";
} else {
	echo "\n     <br><br><br>\n\t Update de onderstaande user:<br>";
	echo $inputfldusr;
	echo $inputfldpass;
}
if (!isset($inputfldemail) || $inputfldemail == "" || $inputfldemail == NULL ) {
  	echo "\t<input type=\"text\" name=\"iEMAIL\" length=\"100\" size=\"53\"><br>\n";
} else {
	echo $inputfldemail;
}
echo "\t{$grouplist}\t</select><br><br>";
if (isset($_GET['action']) && ($_GET['action'] == 'changeusr')) {
	echo "\n\t<input type=\"hidden\" value=\"{$userrow["id"]}\" name=\"USRID\">";
	echo "\n\t<input type=\"submit\" value=\"Update user\">";
	echo "\n\t<input type=\"hidden\" name=\"CHANGEUSER\">";
} else {
	echo "\n\t<input type=\"submit\" value=\"Voer user in\">";
	echo "\n\t<input type=\"hidden\" name=\"ADDUSER\">";
}
echo "\n   </form>";

// Make Group form
echo "\n   <form action=\"{$page_ADMIN}\" method=\"post\" name=\"GROUP\">";
if (!isset($inputfldgrp) || $inputfldgrp == "" || $inputfldgrp == NULL ) {
	echo "\n     <br><br><br>\n\t Voer een nieuwe groep in:<br>";
  	echo "\n\t<input type=\"text\" name=\"iGROUP\" length=\"100\" size=\"53\">\n";
} else {
	echo "\n     <br><br><br>\n\t Update onderstaande groep:<br>";
	echo $inputfldgrp;
}
if (isset($_GET['action']) && ($_GET['action'] == 'changegrp')) {
	echo "\n\t<input type=\"hidden\" value=\"{$grouprow["groupid"]}\" name=\"GRPID\">";
	echo "\n\t<input type=\"submit\" value=\"Update groep\">";
	echo "\n\t<input type=\"hidden\" name=\"CHANGEGROUP\">";
} else {
	echo "\n\t<input type=\"submit\" value=\"Voer groep in\">";
	echo "\n\t<input type=\"hidden\" name=\"ADDGROUP\">";
}
echo "\n   </form>";
echo "</div>";
if (isset($debugmode) && ($debugmode == "true")) {
	echo "This page was generated in ".(number_format(microtime(true) - $start_time, 5)). " seconds";
}
dispHtmlfooter();
?>

