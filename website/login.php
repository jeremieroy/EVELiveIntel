<?php 
/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */
session_start();

include("./include/security.php");
include("./include/db_handler.php");
require_once './include/eve_header.php';

if (isset($_POST["ac"]) && $_POST["ac"]=="log") { /// do after login form is submitted  
     if ( isset($_POST["username"]) && isset($_POST["password"])
       && isset( $USERS[$_POST["username"]])
       && $USERS[$_POST["username"]]==$_POST["password"]) { /// check if submitted 

          $igb = new EveHeader();
          $db = new EveDB();
          //
          if( isset( $_POST["track_token"]) && $_POST["track_token"] != "" )
          {
            //if user is tracked            
            $tracked_user = $db->getUser($_POST["track_token"]);
            if($tracked_user ==NULL)
            {
              echo 'Incorrect track token. Please, try again.'; 
            }else{
              $_SESSION["track_token"] = $_POST["track_token"];
              $_SESSION["logged"] = $_POST["username"];
            }           
          }else
          {
            //check if sender is IGB
            $system_id = NULL;
            if($igb->isInGame())
            { 
                $system_id = $igb->getSolarSystemID();
            }

            if($system_id == NULL)
            {
                echo("Please log using the Eve Online Browser or use a track token.");
            }else{

                $db->deleteOldUsers();

                $unique_id = $db->createUser($system_id);
                $_SESSION["logged"]=$_POST["username"];
                $_SESSION["unique_id"] = $unique_id;
            }
          }
     } else { 
          echo 'Incorrect password. Please, try again.'; 
     }; 
}; 

if (isset($_SESSION["logged"]) &&  array_key_exists($_SESSION["logged"],$USERS)) { //// check if user is logged or not  
      //var_dump($_SESSION);
      header("Location: index.php");      
} else { //// if not logged show login form 
  ?>
<!DOCTYPE html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1" />
<title>Eve Online Live Intel</title>
</head>
<body>
  <div>Give a clue to you member about where to find/or ask the password...<param name="s" value=""></div>
  <form action="login.php" method="post"><input type="hidden" name="ac" value="log">
    <input type="hidden" name="username" value="member" />
    <!-- Username: <input type="text" name="username" /> -->
     Password: <input type="password" name="password" /><br/>
     Tracking token: <input type="text" name="track_token" value="" /> (optional)<br/>
    <input type="submit" value="Login" />
  </form>
</body>

<?php
}; 
?>