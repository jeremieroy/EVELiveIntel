<?php
/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */

class EveHeader{
    private $headers;
    function __construct() {
        $this->headers = $_SERVER;
    }

    function isInGame() { return ($this->headers['HTTP_EVE_TRUSTED']=="Yes"); }
    function getCharName() { return ($this->headers['HTTP_EVE_CHARNAME']); }
    function getCharID() { return ($this->headers['HTTP_EVE_CHARID']); }
    function getSolarSystemName() { return ($this->headers['HTTP_EVE_SOLARSYSTEMNAME']); }
    function getSolarSystemID() { return ($this->headers['HTTP_EVE_SOLARSYSTEMID']); }
}

?>