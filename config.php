<?php

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
$db_PASS = "u3yr8rgvjGEBFfvjweErEI";
$db_HOST = "127.0.0.1";
$db_TBLNAME = "list";
$db_TBLNAMEU = "users";
$db_TBLNAMEG = "groups";

$page_SELF = "https://domain.tld/list/";
$page_ADMIN = "{$page_SELF}admin.php";

$logenable = "true";
$debugmode = "false";
// Override the default above
//$logenable = "false";
//$debugmode = "true";

?>
