<?php
// Alle dingen lijst
// Credits: bartjan@pc-mania.nl
include './config.php';
include './functions.php';

session_handler();

if ($_SESSION['admin'] != 1) {
    dispHtmlErrorpage("NO_RIGHTS", "");
    exit();
}

// Build of mainpage from here
dispHtmlHeader();

// Add new item to DB
if (isset($_POST['ADDUSER'])) {
    if ($_POST['iUSER'] == "" || $_POST['iUSER'] == NULL || $_SESSION['username'] == "" || $_POST['iEMAIL'] == "" || $_POST['iEMAIL'] == NULL || $_POST['iGROUP'] == 0) {
        dispHtmlErrorpage("NO_ITEM", "");
        exit();
    } else {
        $user = $_POST['iUSER'];
        $email = $_POST['iEMAIL'];
        $q = $conn->prepare("INSERT INTO `{$db_TBLNAMEU}` (`username`, `emailadres`, `groupid`, `active`) VALUES (?,?,?,1)");
        $q->bind_param('sss', $user, $email, $_POST['iGROUP']);
        $q->execute();
        header("Location: $page_ADMIN");
    }
}
if (isset($_POST['CHANGEUSER'])) {
    if ($_POST['iUSER'] == "" || $_POST['iUSER'] == NULL || $_SESSION['username'] == "" || $_POST['iEMAIL'] == "" || $_POST['iEMAIL'] == NULL || $_POST['iGROUP'] == 0) {
        dispHtmlErrorpage("NO_ITEM", "");
        exit();
    } else {
        $user = $_POST['iUSER'];
        $email = $_POST['iEMAIL'];
        $password = $_POST['iPASS'];
        if (strlen($password) <= 7) {
            dispHtmlErrorpage("SHORTPASS", "");
            exit();
        }
        $options = ['cost' => 12];
        $encrpass = password_hash($password, PASSWORD_BCRYPT, $options);
        $q = $conn->prepare("UPDATE `{$db_TBLNAMEU}` SET `username` = ?, `password` = ?, `emailadres` = ?, `groupid` = ? WHERE `id` = ?");
        $q->bind_param('sssii', $user, $password, $email, $_POST['iGROUP'], $_POST['USRID']);
        $q->execute();
        header("Location: $page_ADMIN");
    }
}
if (isset($_POST['CHANGEGROUP'])) {
    if ($_POST['iGRP'] == "" || $_POST['iGRP'] == NULL || $_SESSION['username'] == "") {
        dispHtmlErrorpage("NO_ITEM", "");
        exit();
    } else {
        $user = $_POST['iGRP'];
        $q = $conn->prepare("UPDATE `{$db_TBLNAMEG}` SET `groupname` = ? WHERE `groupid` = ?");
        $q->bind_param('si', $_POST['iGRP'], $_POST['GRPID']);
        $q->execute();
        header("Location: $page_ADMIN");
    }
}
if (isset($_POST['ADDGROUP'])) {
    if ($_POST['iGROUP'] == "" || $_POST['iGROUP'] == NULL || $_SESSION['username'] == "") {
        dispHtmlErrorpage("NO_ITEM", "");
        exit();
    } else {
        $group = $_POST['iGROUP'];
        $q = $conn->prepare("INSERT INTO `{$db_TBLNAMEG}` (`groupname`, `active`) VALUES (?,1)");
        $q->bind_param('s', $group);
        $q->execute();
        header("Location: $page_ADMIN");
    }
}

// Modify or delete item
if (isset($_GET['action']) && empty($_GET['id'])) {
    dispHtmlErrorpage("NO_ID", "");
    exit();
} elseif (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'deleteusr') {
        $q = $conn->prepare("UPDATE `{$db_TBLNAMEU}` SET `active` = '0' WHERE `id` = ?");
        $q->bind_param('i', $id);
        $q->execute();
        header("Location: $page_ADMIN");
    }
    if ($_GET['action'] == 'deletegrp') {
        $q = $conn->prepare("SELECT * FROM `{$db_TBLNAMEU}` WHERE `groupid` = ?");
        $q->bind_param('i', $id);
        $q->execute();
        $result = $q->get_result();
        if (!$result->num_rows == 0) {
            dispHtmlErrorpage("GROUP_NOTEMPTY", "");
            exit();
        } elseif ($result->num_rows == 0) {
            $q = $conn->prepare("UPDATE `{$db_TBLNAMEG}` SET `active` = '0' WHERE `groupid` = ?");
            $q->bind_param('i', $id);
            $q->execute();
            header("Location: $page_ADMIN");
        }
        $result->free_result();
    }
    if (($_GET['action'] == 'changeusr')) {
        $q = $conn->prepare("SELECT `id`, `username`, `password`, `emailadres`, `groupid` FROM `{$db_TBLNAMEU}` WHERE `id` = ?");
        $q->bind_param('i', $id);
        $q->execute();
        $result = $q->get_result();
        if ($result->num_rows == 0) {
            header("Location: $page_ADMIN");
        } elseif ($result->num_rows == 1) {
            $userrow = $result->fetch_assoc();
            $inputfldusr = "<input type=\"text\" name=\"iUSER\" length=\"100\" size=\"53\" value=\"{$userrow['username']}\"><br>\n";
            $inputfldemail = "<input type=\"text\" name=\"iEMAIL\" length=\"100\" size=\"53\" value=\"{$userrow['emailadres']}\"><br>\n";
            if ($userrow['password'] == "") {
                $inputfldpass = "<input type=\"password\" name=\"iPASS\" length=\"100\" size=\"53\" value=\"\"><br>\n";
            } elseif (strlen($userrow['emailadres']) > 2) {
                $inputfldpass = "<input type=\"password\" name=\"iPASS\" length=\"100\" size=\"53\" value=\"blankpassword\"><br>\n";
            }
        }
        $result->free_result();
    }
    if ($_GET['action'] == 'changegrp') {
        $q = $conn->prepare("SELECT `groupid`, `groupname` FROM `{$db_TBLNAMEG}` WHERE `groupid` = ?");
        $q->bind_param('i', $id);
        $q->execute();
        $result = $q->get_result();
        if ($result->num_rows == 0) {
            header("Location: $page_ADMIN");
        } elseif ($result->num_rows == 1) {
            $grouprow = $result->fetch_assoc();
            $inputfldgrp = "<input type=\"text\" name=\"iGRP\" length=\"100\" size=\"53\" value=\"{$grouprow['groupname']}\"><br>\n";
        }
        $result->free_result();
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
$q = $conn->prepare("SELECT * FROM `users` WHERE `active` = 1");
$q->execute();
$result = $q->get_result();

while ($usrsrow = $result->fetch_assoc()) {
    echo "\n   <tr>\n     <td class=\"admin\" width=\"200px\">{$usrsrow["username"]}</td>\n";
    echo "     <td class=\"admin\" width=\"200px\">{$usrsrow["emailadres"]}</td>\n";
    echo "     <td class=\"admin\" width=\"200px\">{$usrsrow["groupid"]}</td>\n";
    echo "     <td class=\"admin\"><a href=\"{$page_ADMIN}?action=changeusr&amp;id={$usrsrow["id"]}\"><span class=\"ink-label gray\">Wijzig</span></a></td>\n";
    echo "     <td class=\"admin\"><a href=\"{$page_ADMIN}?action=deleteusr&amp;id={$usrsrow["id"]}\"><span class=\"ink-label gray\">Verwijder</span></a></td>";
    echo "\n   </tr>";
}
$result->free_result();

echo "</table>\n<br>\n<br>\nGroups\n<br>\n<br>\n";
echo "<table class=\"admin\">";
echo "\n   <tr><th>Groupname</th><th>Group</th><th></th><th></th></tr>\n";

// Get Groups
$q = $conn->prepare("SELECT * FROM `groups` WHERE `active` = 1 ORDER BY `groupid`");
$q->execute();
$result = $q->get_result();

$grouplist = "<select name=\"iGROUP\"><option value=\"0\">Selecteer groep</option>\n";
while ($grprow = $result->fetch_assoc()) {
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
$result->free_result();

echo "\n   </table>";

// Make User form
echo "\n   <form action=\"{$page_ADMIN}\" method=\"post\" name=\"USER\">";
if (!isset($inputfldusr) || $inputfldusr == "" || $inputfldusr == NULL) {
    echo "\n     <br><br><br>\n\t Voer een nieuwe user in:<br>";
    echo "\n\t<input type=\"text\" name=\"iUSER\" length=\"100\" size=\"53\">\n";
} else {
    echo "\n     <br><br><br>\n\t Update de onderstaande user:<br>";
    echo $inputfldusr;
    echo $inputfldpass;
}
if (!isset($inputfldemail) || $inputfldemail == "" || $inputfldemail == NULL) {
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
if (!isset($inputfldgrp) || $inputfldgrp == "" || $inputfldgrp == NULL) {
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
dispHtmlfooter();
