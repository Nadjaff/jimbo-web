<?php
//include database connection
include 'libs/db_connect.php';

try{
	
	$filename = "";

	if ( is_uploaded_file($_FILES['uploadFile']['tmp_name']) ) {
		$filename = 'test' . uniqid() . '.jpg';
		move_uploaded_file($_FILES['uploadFile']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/testbot/testuploads/' . $filename);
		
	}


	//write query
	$query = "INSERT INTO tests" . $_GET["s"] . " SET method = ?, url = ?, headers = ?, payload = ?";
	if ($filename != ""){
		$query .= ", attachment = ?";
	}

	//prepare query for excecution
	$stmt = $con->prepare($query);

	//bind the parameters
	//this is the first question mark
	$stmt->bindParam(1, $_REQUEST['method']);

	//this is the second question mark
	$stmt->bindParam(2, $_REQUEST['url']);

	//this is the third question mark
	$stmt->bindParam(3, $_REQUEST['headers']);

	//this is the fourth question mark
	$stmt->bindParam(4, $_REQUEST['payload']);

	if ($filename != ""){
		//this is the fourth question mark
		$stmt->bindParam(5, $filename);
	}

	// Execute the query
	if($stmt->execute()){
		echo "Test was created.";
	}else{
		echo "Unable to create test.";
	}
}

//to handle error
catch(PDOException $exception){
	echo "Error: " . $exception->getMessage();
}
?>