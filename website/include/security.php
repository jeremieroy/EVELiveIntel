<?php
/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */

//replace this password by something meaningful
$USERS["member"] = "dummy";

//minimal security check, relocate to login.php if not logged
function check_logged() {
    global $_SESSION, $USERS;    
    if (!array_key_exists($_SESSION["logged"],$USERS)) {
        header("Location: login.php");
    }
}

?>