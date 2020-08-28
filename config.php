<?php

$version = "1.6";

// Prevents javascript XSS attacks aimed to steal the session ID,
// Adds entropy into the randomization of the session ID, as PHP's random number
// generator has some known flaws, uses a strong hash, and Session ID cannot be passed through URLs
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.entropy_file', '/dev/urandom');
ini_set('session.hash_function', 'whirlpool');
ini_set('session.use_only_cookies', 1);

// Variables
$db_NAME = "webroot";
$db_USER = "webroot";
$db_PASS = "dAppnqNbJghBLWSI";
$db_HOST = "localhost";
$db_TBLNAME = "lijst";
$db_TBLNAMEU = "users";
$db_TBLNAMEG = "groups";

$page_SELF = "https://uxx-002.pc-mania.nl/lijst/";
$page_ADMIN = "{$page_SELF}admin.php";

$logenable = "true";
$debugmode = "false";
// Override the default above
//$logenable = "false";
//$debugmode = "true";

?>
