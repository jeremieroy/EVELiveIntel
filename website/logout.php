<?php
/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */
session_start();
if(isset($_SESSION["unique_id"]))
{
	include("./include/db_handler.php");
	$db = new EveDB();
	$db->deleteUser($_SESSION["unique_id"]);
}

// Unset all of the session variables.
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: login.php"); 
?>