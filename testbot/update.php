<?php
//include database connection
include 'libs/db_connect.php';
include 'config.inc';

try{
	$filename = $_REQUEST['attachment'];

	if ( is_uploaded_file($_FILES['uploadFile']['tmp_name']) ) {
		$filename = 'test' . uniqid() . '.jpg';
		move_uploaded_file($_FILES['uploadFile']['tmp_name'], 
			$testbot_testuploads . $filename);
		
	}
	
	if ($filename == NULL){
		$filename = "";
	}
	//write query
	//in this case, it seemed like we have so many fields to pass and 
	//its kinda better if we'll label them and not use question marks
	//like what we used here
	$query = "update 
				tests" . $_GET["s"] . " 
			set 
				method = :method, 
				url = :url, 
				headers = :headers, 
				payload = :payload, 
				";
	//if ($filename != ""){
		$query .= "attachment = :attachment,
					";
	//}
	$query .= "returnval = :returnval,
			   ignorekeys = :ignorekeys
			where
				id = :id";

	//prepare query for excecution
	$stmt = $con->prepare($query);

	//bind the parameters
	$stmt->bindParam(':method', $_REQUEST['method']);
	$stmt->bindParam(':url', $_REQUEST['url']);
	$stmt->bindParam(':headers', $_REQUEST['headers']);
	$stmt->bindParam(':payload', $_REQUEST['payload']);
	$stmt->bindParam(':returnval', $_REQUEST['returnval']);
	$stmt->bindParam(':ignorekeys', $_REQUEST['ignorekeys']);
	
	if ($filename != ""){
		//this is the fourth question mark
		$stmt->bindParam(':attachment', $filename);
	}else{
		$stmt->bindParam(':attachment', $_REQUEST['attachment']);
	}

	$stmt->bindParam(':id', $_REQUEST['id']);
	
	// Execute the query
	if($stmt->execute()){
	}else{
		echo "Unable to update test.";
	}

}

//to handle error
catch(PDOException $exception){
	echo "Error: " . $exception->getMessage();
}
?>