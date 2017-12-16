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
        //include_once dirname(__FILE__) . '/Config.php';
        include_once dirname(__FILE__) . '/../../../config.php';
		
		

//$host = "127.0.0.1";
$host = "internal-db.s191899.gridserver.com";
$db_name = "db191899_jimbodb";
$username = "db191899";
$password = "Eoh1wZ4#!x";//"Ru0*2.495OuS37n";
$host = DB_HOST;
$db_name = DB_NAME;
$username = DB_USER;
$password = DB_PASSWORD;
$port = DB_PORT;

try {
	$this->conn = new PDO("mysql:host={$host};port={$port};dbname={$db_name}", $username, $password);
	$this->conn->query("SET SESSION time_zone = '+0:00'"); 
}catch(PDOException $exception){ //to handle connection error
	echo "Connection error: " . $exception->getMessage();
}
        $this->conni = new mysqli($host . ":" . $port, $username, $password, $db_name);

        // returing connection resource
        return $this->conn;
    }
	function connecti(){
$host = "localhost";
$db_name = "jimbo";
$username = "root";
$password = "";//"Ru0*2.495OuS37n";
$host = DB_HOST;
$db_name = DB_NAME;
$username = DB_USER;
$password = DB_PASSWORD;
$port = DB_PORT;

        $this->conni = new mysqli($host . ":" . $port, $username, $password, $db_name);
        return $this->conni;
	}

}

?>
