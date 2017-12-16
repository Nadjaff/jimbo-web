<?php
//include database connection
include 'libs/db_connect.php';

try{

	//write query
	//in this case, it seemed like we have so many fields to pass and 
	//its kinda better if we'll label them and not use question marks
	//like what we used here
	$query = "update 
				tests" . $_GET["s"] . " 
			set 
				returnval = :returnval
			where
				id = :id";

	//prepare query for excecution
	$stmt = $con->prepare($query);

	//bind the parameters
	$stmt->bindParam(':returnval', $_REQUEST['returnval']);

	$stmt->bindParam(':id', $_REQUEST['id']);

	// Execute the query
	if($stmt->execute()){
		echo "Successfully accepted" . $_REQUEST['returnval'];
	}else{
		echo "Unable to accept test";
	}

}

//to handle error
catch(PDOException $exception){
	echo "Error: " . $exception->getMessage();
}
?>