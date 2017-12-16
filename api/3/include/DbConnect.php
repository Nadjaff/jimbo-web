<?php

/**
 * Handling database connection
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbConnect {

    private $conn;

    function __construct() {        
    }

    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect() {
        include_once dirname(__FILE__) . '/Config.php';
		
		

//$host = "127.0.0.1";
$host = "internal-db.s191899.gridserver.com";
$db_name = "db191899_jimbodb";
$username = "db191899";
$password = "Eoh1wZ4#!x";//"Ru0*2.495OuS37n";

try {
	$this->conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
	$this->conn->query("SET SESSION time_zone = '+0:00'"); 
}catch(PDOException $exception){ //to handle connection error
	echo "Connection error: " . $exception->getMessage();
}
        $this->conni = new mysqli($host, $username, $password, $db_name);

        // returing connection resource
        return $this->conn;
    }
	function connecti(){
$host = "internal-db.s191899.gridserver.com";
$db_name = "db191899_jimbodb";
$username = "db191899";
$password = "Eoh1wZ4#!x";//"Ru0*2.495OuS37n";
        $this->conni = new mysqli($host, $username, $password, $db_name);
        return $this->conni;
	}

}

?>
