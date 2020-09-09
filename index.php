<?php
// Alle dingen lijst
// Credits: bartjan@pc-mania.nl

include './config.php';
include './functions.php';

session_handler();

if (isset($_GET)) {
    if (isset($_GET['cl'])) {
        // List Switching
        if ($_GET['cl'] == 1) {
            $_SESSION['show_list'] = '1';
            $_SESSION['buttonleft'] = 'gehaald';
            $_SESSION['item'] = 'nieuwe boodschap';
            $_SESSION['heading'] = "   <h2>Boodschappen</h2>\n   De te halen items zijn:\n   <br><br>";
        } elseif ($_GET['cl'] == 2) {
            $_SESSION['show_list'] = '2';
            $_SESSION['buttonleft'] = 'bezocht';
            $_SESSION['item'] = 'nieuw dagje uit';
            $_SESSION['heading'] = "   <h2>Dagje weg</h2>\n   De te bezoeken plaatsen zijn:\n   <br><br>";
        } elseif ($_GET['cl'] == 3) {
            $_SESSION['show_list'] = '3';
            $_SESSION['buttonleft'] = 'gekeken';
            $_SESSION['item'] = 'nieuwe te kijken film';
            $_SESSION['heading'] = "   <h2>Films</h2>\n   De te bekijken films zijn:\n   <br><br>";
        } elseif ($_GET['cl'] == 4) {
            $_SESSION['show_list'] = '4';
            $_SESSION['buttonleft'] = 'gedaan';
            $_SESSION['item'] = 'nieuwe uit te voeren klus';
            $_SESSION['heading'] = "   <h2>Klusjes</h2>\n   De uit te voeren klusjes zijn:\n   <br><br>";
        } else {
            $_SESSION['show_list'] = '1';
            $_SESSION['buttonleft'] = 'gehaald';
            $_SESSION['item'] = 'nieuwe boodschap';
            $_SESSION['heading'] = "   <h2>Boodschappen</h2>\n   De te halen items zijn:\n   <br><br>";
        }
        header("Location: $page_SELF");
    } elseif (isset($_GET['action'])) {
        // Modify or delete item
        if (empty($_GET['id'])) {
            dispHtmlErrorpage("NO_ID", "");
            exit();
        } elseif ($_GET['action'] == 'delete') {
            $id = $_GET['id'];
            // Deletion of item
            $q = $conn->prepare("UPDATE `{$db_TBLNAME}` SET `active` = '0', `datedeleted` = CURRENT_TIMESTAMP, `deletedby` = ? WHERE `id` = ? AND `groupid` = ?");
            $q->bind_param('sii', $_SESSION['username'], $id, $_SESSION['groupid']);
            $q->execute();
            header("Location: $page_SELF");
        } elseif ($_GET['action'] == 'ordered') {
            $id = $_GET['id'];
            // Marking of ordered item
            $q = $conn->prepare("UPDATE `{$db_TBLNAME}` SET `ordered` = '1', `datemodified` = CURRENT_TIMESTAMP, `changedby` = ? WHERE `id` = ? AND `groupid` = ?");
            $q->bind_param('sii', $_SESSION['username'], $id, $_SESSION['groupid']);
            $q->execute();
            header("Location: $page_SELF");
        } else {
            header("Location: $page_SELF");
        }
    } elseif (!empty($_GET)) {
        // Invalid input or someone trying to try RFI stuff
        header("HTTP/1.0 404 Not Found");
        exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested URL was not found on this server.</p>\n</body></html>\n");
    }
}

if (isset($_POST)) {
    // Add new item to DB
    if (isset($_POST['ADD'])) {
        if ($_POST['ITEM'] == "" || $_POST['ITEM'] == NULL || $_SESSION['username'] == "") {
            dispHtmlErrorpage("NO_ITEM", "");
            exit();
        } else {
            $item = $_POST['ITEM'];
            $q = $conn->prepare("INSERT INTO `{$db_TBLNAME}` (item,datedeleted,owner,list,emailout,groupid) VALUES (?,NULL,?,?,0,?)");
            $q->bind_param('ssii', $item, $_SESSION['username'], $_SESSION['show_list'], $_SESSION['groupid']);
            $q->execute();
            header("Location: $page_SELF");
        }
    } elseif (!empty($_POST)) {
        // Invalid input or someone trying to try RFI stuff
        header("HTTP/1.0 404 Not Found");
        exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested URL was not found on this server.</p>\n</body></html>\n");
    }

}


// Build of the main page from here
dispHtmlHeader();
echo " <div id=\"page\">\n";
if ($_SESSION['show_list'] == "1") {
    echo "   <h4>boodschappen | <a href=\"{$page_SELF}index.php?cl=2\" class=header>dagje weg</a> | <a href=\"{$page_SELF}index.php?cl=3\" class=header>films</a> | <a href=\"{$page_SELF}index.php?cl=4\" class=header>klus</a> |";
}
if ($_SESSION['show_list'] == "2") {
    echo "   <h4><a href=\"{$page_SELF}index.php?cl=1\" class=header>boodschappen</a> | dagje weg | <a href=\"{$page_SELF}index.php?cl=3\" class=header>films</a> | <a href=\"{$page_SELF}index.php?cl=4\" class=header>klus</a> |";
}
if ($_SESSION['show_list'] == "3") {
    echo "   <h4><a href=\"{$page_SELF}index.php?cl=1\" class=header>boodschappen</a> | <a href=\"{$page_SELF}index.php?cl=2\" class=header>dagje weg</a> | films | <a href=\"{$page_SELF}index.php?cl=4\" class=header>klus</a> |";
}
if ($_SESSION['show_list'] == "4") {
    echo "   <h4><a href=\"{$page_SELF}index.php?cl=1\" class=header>boodschappen</a> | <a href=\"{$page_SELF}index.php?cl=2\" class=header>dagje weg</a> | <a href=\"{$page_SELF}index.php?cl=3\" class=header>films</a> | klus |";
}
if ($_SESSION['admin'] == "1") {
    echo " <a href=\"{$page_ADMIN}\" class=header>admin</a> |";
}
echo " <a href=\"{$page_SELF}index.php?action=logout\" class=header>logout</a></h4>\n";
echo $_SESSION['heading'];

// Get current items on active list from DB

$sql = "SELECT * FROM `{$db_TBLNAME}` WHERE `active` = 1 AND `list` = ? AND `groupid` = ? ORDER BY `id`";
if ($q = $conn->prepare($sql)) {
    $q->bind_param('ii', $_SESSION['show_list'], $_SESSION['groupid']);
    $q->execute();
    $result = $q->get_result();
} else {
    $error = $conn->errno . ' ' . $conn->error;
    echo $error;
}

echo "\n   <table>";
while ($row = $result->fetch_assoc()) {

    if ($row['ordered'] == 1) {
        $color = "green";
    } else {
        $color = "red";
    }
    echo "\n   <tr class=\"{$color}\">\n     <td class=\"{$color}\" width=\"200px\">{$row["item"]}</td>\n";
    echo "     <td class=\"{$color}\"><a href=\"{$page_SELF}index.php?action=ordered&amp;id={$row["id"]}\"><span class=\"ink-label gray\">{$_SESSION['buttonleft']}</span></a></td>\n";
    echo "     <td class=\"{$color}\"><a href=\"{$page_SELF}index.php?action=delete&amp;id={$row["id"]}\"><span class=\"ink-label gray\">verwijder</span></a></td>";
    echo "\n   </tr>";
}
$result->free_result();
echo "\n   </table>";
echo "\n   <form action=\"{$page_SELF}\" method=\"post\">";
echo "\n     <br><br><br>\n\t Voer een {$_SESSION['item']} in:<br>";
echo "\n     <input type=\"text\" name=\"ITEM\" length=\"100\" size=\"53\"><br>";
echo "\n     <input type=\"submit\" value=\"Voer in\">";
echo "\n     <input type=\"hidden\" name=\"ADD\">";
echo "\n   </form>";
echo "\n </div>";

dispHtmlfooter();
