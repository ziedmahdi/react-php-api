<?php

class DatabaseService {
    function __construct($db_host, $db_name, $db_user, $db_password) {
        $this->db_host = $db_host;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_password = $db_password;
        
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name, $this->db_user, $this->db_password);
        } catch(PDOException $exception){
            echo "Connection failed: " . $exception->getMessage();
        }
    }
 
    private $db_host;
    private $db_name;
    private $db_user;
    private $db_password;
    private $conn;

    public function getConnection(){
        return $this->conn;
    }
}
?>