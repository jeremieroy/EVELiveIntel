<?php
/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */
session_start(); /// initialize session
include("../include/security.php");
check_logged();

include("../include/common.php");
$db = new EveDB();
$igb = new EveHeader();

update_local_system($db, $igb);
if($local_system_id == NULL)
{        
    $response = array();
    $response['error'] = true;
    $response['message'] = "Invalid system";
    echo_JSON(200, $response);
    return;
}

if(!isset($_GET["status"]) )
{        
    $response = array();
    $response['error'] = true;
    $response['message'] = "Invalid status";
    echo_JSON(200, $response);
    return;
}

$status = $_GET["status"];
if($status != 0 && $status !=1  && $status != 2)
{
    $response = array();
    $response['error'] = true;
    $response['message'] = "Invalid status";
    echo_JSON(200, $response);
    return;
}

$db->updateIntel($local_system_id, $status, $user_token);

$response = array();
$response['error'] = false;
$response['message'] = "Intel updated";
echo_JSON(200, $response);
?>