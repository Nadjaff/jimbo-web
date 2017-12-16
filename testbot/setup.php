<?php
$host = "localhost";
$port = 8889;
$username = "root";
$password = "root";
$db_name = 'testbot';

try {
    $con = new PDO("mysql:host={$host};port={$port};dbname={$db_name}", $username, $password);
}catch(PDOException $exception){ //to handle connection error
    echo "Connection error: " . $exception->getMessage();
}


$table = array();

$table[] = "CREATE TABLE tests (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    apiversion VARCHAR(5) NOT NULL,
    method VARCHAR(9) NOT NULL,
    url VARCHAR(30),
    headers VARCHAR(128),
    attachment VARCHAR(30),
    returnval VARCHAR(128),
    ignorekeys VARCHAR(128)
    )";

$table[] = "DESCRIBE tests";

foreach($table as $sql){
    $query = $con->query($sql);
    print_r($con->errorInfo());
}

if(!$query){
    echo "Error creating tables: ";
    print_r($con->errorInfo());
}else{
    echo "All tables has been created successfully";
}

?>