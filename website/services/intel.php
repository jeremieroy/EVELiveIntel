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
    $response['message'] = "Tracking token expired. Please logout.";
    echo_JSON(200, $response);
    return;
}

if(isset($_GET["last_update"]))
{
    $intel_list =  $db->getIntelByTime($_GET["last_update"]);   
}else{
    $intel_list =  $db->getLastIntel();   
}

$response = array();
$response["error"] = false;
$response["serverTime"] = date("Y-m-d H:i:s", time());
$response["system_id"] = $local_system_id;
//$response["system_name"] = $local_system_name;

$response["intels"] = $intel_list;


$trackers = array();

if( isset($_GET["t1"]) && $_GET["t1"]!="")
{
    $target_user = $db->getUser(trim($_GET["t1"]));
    if($target_user!=NULL)
    {
        $trackers["t1"] = $target_user["system_id"];
    }
}

if( isset($_GET["t2"]) && $_GET["t2"]!="")
{
    $target_user = $db->getUser(trim($_GET["t2"]));
    if($target_user!=NULL)
    {
        $trackers["t2"] = $target_user["system_id"];
    }
}

if( isset($_GET["t3"]) && $_GET["t3"]!="")
{
    $target_user = $db->getUser(trim($_GET["t3"]));
    if($target_user!=NULL)
    {
        $trackers["t3"] = $target_user["system_id"];
    }
}

$response["trackers"] = $trackers;
echo_JSON(200, $response);

?>