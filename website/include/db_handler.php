<?php
 /* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */
 
class iimysqli_result
{
    public $stmt, $nCols;
} 

function stmt_get_result($stmt)
{
    $stmt->store_result();

    $meta = $stmt->result_metadata(); 
    while ($field = $meta->fetch_field()) 
    { 
        $params[] = &$row[$field->name]; 
    } 

    call_user_func_array(array($stmt, 'bind_result'), $params); 

    while ($stmt->fetch()) { 
        foreach($row as $key => $val) 
        { 
            $c[$key] = $val; 
        } 
        $result[] = $c; 
    } 
    if(isset($result)){
        return $result;
    } else {
        return array();
    }        
}

class EveDB {
 
    public $conn;
 
    function __construct() {
        include_once 'config.php';
         // Connecting to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
        // Check for database connection error
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $sql = "SET time_zone = '+00:00'";
        $result = mysqli_query($this->conn, $sql);
    }
 
    // ******************  USERS MGMT ****************** 

    public function getUser($unique_id) {
        $stmt = $this->conn->prepare("SELECT updated_at, system_id FROM tmp_users WHERE unique_id = ?"); 
        $stmt->bind_param("s", $unique_id); 
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($updated_at,$system_id);        
        $stmt->fetch();
        $stmt->close();

        if($updated_at == NULL)
        {               
            return NULL;        
        }        

        $res["updated_at"]=$updated_at;
        $res["system_id"]=$system_id;
        $res["unique_id"]=$unique_id;       
        return  $res;
    }

    function generate_random_string($length) {      
      $random = '';
      for ($i = 0; $i < $length; $i++) {
        $random .= chr(rand(ord('a'), ord('z')));
      }
      return $random;      
    }

    public function deleteUser($unique_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM tmp_users WHERE unique_id = ?");
        $stmt->bind_param("s", $unique_id);
        if ($stmt->execute()) {           
            $stmt->close();            
        } else {
            return NULL;
        }
    }

    //delete old temp users
    public function deleteOldUsers()
    {
        mysqli_query($this->conn,"DELETE FROM tmp_users WHERE updated_at < (NOW() - INTERVAL 8 HOUR");
    }

    //return the unique_id of the created user
    public function createUser($system_id)
    {
        $found = false;
        $count = 5;
        $unique_id = NULL;
        while($found==false && $count-- > 0)
        {
            $unique_id = $this->generate_random_string(5);            
            $usr = $this->getUser($unique_id);
            $found = ($usr == NULL);
        }       
        if($found == false) return NULL;
        
        $stmt = $this->conn->prepare("INSERT INTO tmp_users(unique_id, system_id) values(?, ?)");
        $stmt->bind_param("si", $unique_id, $system_id); 
        $result = $stmt->execute(); 
        $stmt->close();

        return $unique_id;
    }

     public function updateUser($unique_id, $system_id)
     {
        $stmt = $this->conn->prepare("UPDATE tmp_users SET system_id = ?, updated_at = now() WHERE unique_id = ?");
        $stmt->bind_param("is",  $system_id, $unique_id);      
        $result = $stmt->execute();
        $stmt->close();        
        if ($result) {            
            return TRUE;
        } else {        
            return FALSE;
        }
     }   
  
   
    // ******************  INTEL MGMT ******************

    public function updateIntel($system_id, $status, $token) 
    {
        //$stmt = $this->conn->prepare("INSERT INTO intel(system_id, status, updated_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), seen_at=now(), updated_by = VALUES(updated_by)");
        $stmt = $this->conn->prepare("INSERT INTO intel(system_id, status, updated_by) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $system_id, $status,$token);
      
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {            
            return TRUE;
        } else {           
            return FALSE;
        }
    }

    public function resetIntel($system_id, $token) 
    {
        return true;

        $stmt = $this->conn->prepare("UPDATE intel SET status = 3 WHERE system_id = ? AND updated_by = ? AND status = 0");
        $stmt->bind_param("is",  $system_id, $token);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getIntelBySystem($systemList) {
        $systemStr = implode(",", $systemList);
        $sql = "SELECT seen_at, status, system_id FROM intel WHERE system_id IN (".$systemStr.")";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $intels = stmt_get_result($stmt);
        $stmt->close();        
        return $intels;
    }    
    
    public function getIntelByTime($timestamp) {
        $stmt = $this->conn->prepare("SELECT seen_at, status, system_id FROM intel WHERE seen_at > ? ORDER BY seen_at ASC");
        $stmt->bind_param("s", $timestamp);
        $stmt->execute();
        $intels = stmt_get_result($stmt);
        $stmt->close();
        return $intels;
    }

    public function getLastIntel() {
        $stmt = $this->conn->prepare("SELECT seen_at, status, system_id FROM intel WHERE seen_at > (NOW() - INTERVAL 1 HOUR) ORDER BY seen_at ASC");       
        $stmt->execute();
        $intels = stmt_get_result($stmt);
        $stmt->close();
        return $intels;
    }
    
}
 
?>