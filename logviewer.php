<?php
include './config.php';
include './functions.php';

session_handler();

$logsql = "SELECT * FROM (SELECT id,time,clientip,message FROM `logs` ORDER BY id DESC LIMIT 20) sub ORDER BY id ASC";
$logresult = mysqli_query($conn, $logsql) or die ("MySQL query error");

dispHtmlHeader();
echo "\n<body>\n<br>";
echo "<table class=\"admin\">\n <tr>\n  <th>id</th>\n  <th>time</th>\n  <th>clientip</th>\n  <th>message</th>\n </tr>\n";

while ($logrows = mysqli_fetch_assoc($logresult)) {
	echo " <tr>\n  <td class=\"admin\">{$logrows['id']}</td>\n  <td class=\"admin\">{$logrows['time']}</td>\n  <td class=\"admin\">{$logrows['clientip']}</td>\n  <td class=\"admin\">{$logrows['message']}</td>\n </tr>\n";
}
echo "</table>";
echo "<br><br>\n<a href=\"./admin.php\">Back...</a>";
mysqli_free_result($logresult);
dispHtmlfooter();
?>
