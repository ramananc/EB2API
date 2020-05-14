<?php

class Database {
    public $connection;
            
    function __construct() {
        $this->connection = mysqli_connect(Config::DATABASE_HOST, Config::DATABASE_USER, Config::DATABASE_PASS, Config::DATABASE_NAME);
        if (mysqli_connect_errno()) {
                die ('Failed to connect to MySQL: ' . mysqli_connect_error());
        }
    }
    
    function __destruct() {
        mysqli_close($this->connection);
    }

    public function cleanData($data) {
        if ($data === null) {
            return "";
        } else {
            return str_replace(";", "", htmlspecialchars(stripslashes(trim($data))));
        }
    }
    
    public function getData($sql) {
        // TODO: Check to allow only "SELECT" statements...
        $result = mysqli_query($this->connection, $sql);

        if ($result === FALSE) {
            return array("Error" => mysqli_error($this->connection));
        }

        $arr = array();
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                array_push($arr, $row);
            }
        }
        $result->free_result();
        return $arr;
    }
    
    public function setData($sql) {
        // TODO: Check to allow only "UPDATE" and INSERT statements...
        
        $result = mysqli_query($this->connection, $sql);//, MYSQLI_ASYNC);
        if ($result === FALSE) {
            return array("Error" => mysqli_error($this->connection));
        }
        return "";
    }
   
        
    public function deleteData($sql) {
        // TODO: Check to allow only "UPDATE" and INSERT statements...
        
        $result = mysqli_query($this->connection, $sql);//, MYSQLI_ASYNC);
        if ($result === FALSE) {
            return array("Error" => mysqli_error($this->connection));
        }
        return "";
    }
    
    public function getLastInsertId() {
        return mysqli_insert_id($this->connection);
    }
}
?>
