<?php
$host = "internal-db.s191899.gridserver.com";
$db_name = "db191899_jimbodb";
$username = "db191899";//_master2";//"db191899"
$password = "Eoh1wZ4#!x";//"1w@!WhK@i1g";//"9@aIO1tb(t_";//"Ru0*2.495OuS37n";
try {
	$test = 1;
	$conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
		$stmt = $conn->prepare("SELECT * FROM  users LIMIT 0, 30");
		//$stmt->execute();
		if ($stmt->execute()) {
			echo array("users"=>$stmt->fetchAll(PDO::FETCH_ASSOC), "error"=>0);
		}else{
			print_r("error");
			print_r($stmt->errorInfo());
			echo $stmt->errorCode();
			echo array("users"=>$stmt->fetchAll(PDO::FETCH_ASSOC), "error"=>1);
		}
		echo "PHP Code:<br><pre>";
		echo htmlspecialchars(file_get_contents('temp.php'));
		echo "</pre>";
}catch(PDOException $exception){ //to handle connection error
	echo "Connection error: " . $exception->getMessage();
}

	
	
	?>