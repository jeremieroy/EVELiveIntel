<?php
/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */

date_default_timezone_set("UTC");

require_once 'db_handler.php';
require_once 'eve_header.php';

$local_system_id = NULL;
$user_token = "";

function echo_JSON($http_code, $response) {
   	header('Content-type: application/json');
	echo json_encode($response);
}

function update_local_system($db, $igb)
{
	global $local_system_id, $user_token;

	//check if the system id is forced
	if( isset($_GET["system_id"]) )
	{
		$local_system_id = $_GET["system_id"];		
		$user_token = "debug";
		//$db->updateUser($_SESSION["unique_id"], $local_system_id);
	}
	//check if a user is tracked
	else if( isset($_SESSION["track_token"]) )
	{		
		//then local is tracked user system
		$tracked_user = $db->getUser($_SESSION["track_token"]);
		if($tracked_user != NULL)
		{
			$user_token = $tracked_user["unique_id"];
			$local_system_id = $tracked_user["system_id"]; 			
		}
	}
	//check if we are in igb
	else if($igb->isInGame())
    {
    	//then local is the igb current system        
        $local_system_id = $igb->getSolarSystemID();
        //die ("id: ".$local_system_id );
        $db->updateUser($_SESSION["unique_id"], $local_system_id);
        $user_token = $_SESSION["unique_id"];
    }else
    {    	
    	//default to Jita        
        $local_system_id = 30000142;
        $user_token = "debug";
        //$db->updateUser($_SESSION["unique_id"], $local_system_id);
    }
}

?>