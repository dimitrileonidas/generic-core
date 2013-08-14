<?php
namespace core\config;
class DBConfig {

    var $host;
    var $user;
    var $pass;
    var $db;
    var $dbType = "MySql";
    var $pdo;
    
   function conn($host='localhost',$user='root',$pass='',$db='kha'){ // connection function
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
        $dsn = strToLower($this->dbType).":dbname=".$this->db.";host=".$this->host . ';charset=UTF8';
	try {
            $this->pdo = new \PDO (
		$dsn, 
		$user, 
		$pass,
		array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            return $this->pdo;
	 } 
         catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
            exit;
	}
    }

    public function __destruct() { // close connection
       unset($this->pdo);
    }
}
?>