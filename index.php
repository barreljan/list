<?php
// Alle dingen lijst v1.0
// Credits: bartjan@pc-mania.nl

include './config.php';
include './functions.php';

__session_handler__();

// List Switching
if (isset($_GET) && isset($_GET['cl']))  {
	if ($_GET['cl'] == 1) {
                $_SESSION['show_list'] = '1';
                $_SESSION['buttonleft'] = 'gehaald';
                $_SESSION['item'] = 'nieuwe boodschap';
	} elseif ($_GET['cl'] == 2) {
                $_SESSION['show_list'] = '2';
                $_SESSION['buttonleft'] = 'bezocht';
                $_SESSION['item'] = 'nieuw dagje uit';
	} elseif ($_GET['cl'] == 3) {
                $_SESSION['show_list'] = '3';
                $_SESSION['buttonleft'] = 'gekeken';
                $_SESSION['item'] = 'nieuwe te kijken film';
	} else {
                $_SESSION['show_list'] = '1';
                $_SESSION['buttonleft'] = 'gehaald';
                $_SESSION['item'] = 'nieuwe boodschap';
	}
	header("Location: $page_SELF");
}

// Add new item to DB
if (isset($_POST['ADD'])) {
	if ($_POST['ITEM'] == "" || $_POST['ITEM'] == NULL || $_SESSION['username'] == "") {
		__dispHtmlErrorpage__("NO_ITEM","");
		exit();
	} else {
		$item = mysqli_real_escape_string($conn, $_POST['ITEM']); 
		$query = "INSERT INTO `{$db_TBLNAME}` (item,datedeleted,owner,list,emailout,groupid) VALUES (\"{$item}\",NULL,\"{$_SESSION['username']}\",{$_SESSION['show_list']},0,{$_SESSION['groupid']})";
		mysqli_query($conn,$query) or die (__dispHtmlErrorpage__("QUERY_ERR",mysqli_error($conn)));
		header("Location: $page_SELF");
	}
}
// Modify or delete item
if (isset($_GET['action'])) {
	if (isset($_GET['action']) && empty($_GET['id'])) {
		__dispHtmlErrorpage__("NO_ID","");
		exit();
	} elseif ($_GET['action'] == 'delete') {
		$id=$_GET['id'];
		// Deletion of item
		$query = "UPDATE `{$db_TBLNAME}` SET `active` = '0', `datedeleted` = CURRENT_TIMESTAMP, `deletedby` = \"{$_SESSION['username']}\" WHERE `id` = {$id} AND `groupid` = {$_SESSION['groupid']}";
		mysqli_query($conn,$query) or die (__dispHtmlErrorpage__("QUERY_ERR",mysqli_error($conn)));
		header("Location: $page_SELF");
	} elseif ($_GET['action'] == 'ordered') {
		$id=$_GET['id'];
		// Marking of ordered item
		$query = "UPDATE `{$db_TBLNAME}` SET `ordered` = '1', `datemodified` = CURRENT_TIMESTAMP, `changedby` = \"{$_SESSION['username']}\" WHERE `id` = {$id} AND `groupid` = {$_SESSION['groupid']}";
		mysqli_query($conn,$query) or die (__dispHtmlErrorpage__("QUERY_ERR",mysqli_error($conn)));
		header("Location: $page_SELF");
	} else {
		header("Location: $page_SELF");
	}
}

// Build of mainpage from here
__dispHtmlHeader__();
echo " <div id=\"page\">\n";
if ($_SESSION['show_list'] == "1" ) {
	echo "   <h4>boodschappen | <a href=\"{$page_SELF}index.php?cl=2\" class=header>dagje weg</a> |";
	if ($_SESSION['admin'] == "1") {
		echo " <a href=\"{$page_SELF}index.php?cl=3\" class=header>films</a> | <a href=\"{$page_ADMIN}\" class=header>admin</a> |";
		echo " <a href=\"{$page_SELF}index.php?action=logout\" class=header>logout</a></h4>\n";
	} else {
		echo " <a href=\"{$page_SELF}index.php?cl=3\" class=header>films</a> | <a href=\"{$page_SELF}index.php?action=logout\" class=header>logout</a></h4>\n";
	}
	echo "   <h2>Boodschappen</h2>\n";
	echo "   De te halen items zijn:\n   <br><br>";
}
if ($_SESSION['show_list'] == "2" ) {
	echo "   <h4><a href=\"{$page_SELF}index.php?cl=1\" class=header>boodschappen</a> |";
	if ($_SESSION['admin'] == "1") {
		echo " dagje weg | <a href=\"{$page_SELF}index.php?cl=3\" class=header>films</a> |";
		echo " <a href=\"{$page_ADMIN}\" class=header>admin</a> | <a href=\"{$page_SELF}index.php?action=logout\" class=header>logout</a></h4>\n";
	} else {
		echo " dagje weg | <a href=\"{$page_SELF}index.php?cl=3\" class=header>films</a> | <a href=\"{$page_SELF}index.php?action=logout\" class=header>logout</a></h4>\n";
	}
	echo "   <h2>Dagje weg</h2>\n";
	echo "   De te bezoeken plaatsen zijn:\n   <br><br>";
}
if ($_SESSION['show_list'] == "3" ) {
	echo "   <h4><a href=\"{$page_SELF}index.php?cl=1\" class=header>boodschappen</a> |";
	if ($_SESSION['admin'] == "1") {
		echo " <a href=\"{$page_SELF}index.php?cl=2\" class=header>dagje weg</a> | films |";
		echo " <a href=\"{$page_ADMIN}\" class=header>admin</a> | <a href=\"{$page_SELF}index.php?action=logout\" class=header>logout</a></h4>\n";
	} else {
		echo " <a href=\"{$page_SELF}index.php?cl=2\" class=header>dagje weg</a> | films | <a href=\"{$page_SELF}index.php?action=logout\" class=header>logout</a></h4>\n";
	}
	echo "   <h2>Films</h2>\n";
	echo "   De te bekijken films zijn:\n   <br><br>";
}

// Get current items on active list from DB
$query = "SELECT * FROM `{$db_TBLNAME}` WHERE `active` = 1 AND `list` = {$_SESSION['show_list']} AND `groupid` = {$_SESSION['groupid']} ORDER BY `id`";
$items_result = mysqli_query($conn,$query) or die (__dispHtmlErrorpage__("QUERY_ERR",mysqli_error($conn)));
echo "\n   <table>";
while ($row = mysqli_fetch_assoc($items_result)){
	if ($row['ordered'] == 1) {
		$color="green";
	} else {
		$color="red";
	}
	echo "\n   <tr class=\"{$color}\">\n     <td class=\"{$color}\" width=\"200px\">{$row["item"]}</td>\n";
	echo "     <td class=\"{$color}\"><a href=\"{$page_SELF}index.php?action=ordered&amp;id={$row["id"]}\"><span class=\"ink-label gray\">{$_SESSION['buttonleft']}</span></a></td>\n";
	echo "     <td class=\"{$color}\"><a href=\"{$page_SELF}index.php?action=delete&amp;id={$row["id"]}\"><span class=\"ink-label gray\">Verwijder</span></a></td>";
	echo "\n   </tr>";
}
mysqli_free_result($items_result);
echo "\n   </table>";
echo "\n   <form action=\"{$page_SELF}\" method=\"post\">";
echo "\n     <br><br><br>\n\t Voer een {$_SESSION['item']} in:<br>";
echo "\n     <input type=\"text\" name=\"ITEM\" length=\"100\" size=\"53\"><br>";
echo "\n     <input type=\"submit\" value=\"Voer in\">";
echo "\n     <input type=\"hidden\" name=\"ADD\">";
echo "\n   </form>";
echo "\n </div>";
if (isset($debugmode) && ($debugmode == "true")) {
	echo "This page was generated in ".(number_format(microtime(true) - $start_time, 5)). " seconds";
}
__dispHtmlfooter__();
