<?php
//include database connection
include 'libs/db_connect.php';

try {

	$query = "DELETE FROM tests" . $_GET["s"] . " WHERE id = ?";
	$stmt = $con->prepare($query);
	$stmt->bindParam(1, $_POST['id']);
	
	if($stmt->execute()){
	}else{
		echo "Unable to delete test.";
	}
	
}

//to handle error
catch(PDOException $exception){
	echo "Error: " . $exception->getMessage();
}
?>