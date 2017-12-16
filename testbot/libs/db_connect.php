<?php
/*$host = "localhost";
$db_name = "stepheno_jimbotesting";
$username = "stepheno_jtest";
$password = ")TZVIHmpI]n#";*/
$host = "internal-db.s191899.gridserver.com";
$db_name = "db191899_jimboapitestbot";
$username = "db191899_tester";
$password = "!9l#klHVk9!";//"kn;YW*2Sa#P&M.R";

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
?>