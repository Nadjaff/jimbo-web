<?php
//include database connection
include 'libs/db_connect.php';
include "runtest.php";
$filtered = false;

if ($_REQUEST["type"] == NULL || $_REQUEST["type"] == "" || $_REQUEST["type"] == "Changed" || $_REQUEST["type"] == "All"){
	$query = "SELECT id, method, url, headers, payload, returnval, attachment FROM tests";
	$stmt = $con->prepare( $query );
	$stmt->execute();
	if (($_REQUEST["type"] == "Changed")){
		$changes = true;
	}else{
		$changes = false;
	}
	displayResult($stmt,$changes);
}else{
	echo "<h1>GET</h1>";
	$query = "SELECT id, method, url, headers, payload, returnval, attachment FROM tests WHERE url=:url AND method='GET'";
	$stmt = $con->prepare( $query );
	$stmt->bindParam(':url', $_REQUEST['type']);
	$stmt->execute();
	displayResult($stmt);
	
	
	echo "<h1>POST</h1>";
	$query = "SELECT id, method, url, headers, payload, returnval, attachment FROM tests WHERE url=:url AND method='POST'";
	$stmt = $con->prepare( $query );
	$stmt->bindParam(':url', $_REQUEST['type']);
	$stmt->execute();
	displayResult($stmt);
	
	
	
	echo "<h1>UPDATE</h1>";
	$query = "SELECT id, method, url, headers, payload, returnval, attachment FROM tests WHERE url=:url AND method='UPDATE'";
	$stmt = $con->prepare( $query );
	$stmt->bindParam(':url', $_REQUEST['type']);
	$stmt->execute();
	displayResult($stmt);
	
	
	
	echo "<h1>DELETE</h1>";
	$query = "SELECT id, method, url, headers, payload, returnval, attachment FROM tests WHERE url=:url AND method='DELETE'";
	$stmt = $con->prepare( $query );
	$stmt->bindParam(':url', $_REQUEST['type']);
	$stmt->execute();
	displayResult($stmt);
	
	
}

//select all data

//this is how to get number of rows returned
function displayResult($stmt,$changes=false){
if($stmt->rowCount()>0){ //check if more than 0 record found

    echo "<table id='tfhover' class='tftable' border='1'>";//start table
    
        //creating our table heading
        echo "<tr>";
            echo "<th>Method</th>";
            echo "<th>URL</th>";
            echo "<th>Time Taken</th>";
            echo "<th>Headers</th>";
            echo "<th>Payload</th>";
            echo "<th>Return</th>";
            echo "<th>New Return</th>";
            echo "<th>Accept Change</th>";
            echo "<th style='text-align:center;'>Action</th>";
        echo "</tr>";
        
        //retrieve our table contents
        //fetch() is faster than fetchAll()
        //http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            //extract row
            //this will make $row['firstname'] to
            //just $firstname only
            extract($row);
			$testresult = runTest($method,$url,$headers,$payload,$attachment);
			$returnval = stripslashes($returnval);
            
			if($changes == false || $returnval != $testresult[0]){
					
            //creating new table row per record
            	echo "<tr>";
                echo "<td>{$method}</td>";
                echo "<td>{$url}</td>";
                echo "<td>" . $testresult[1] . "</td>";
                echo "<td>{$headers}";
				if ($attachment != ""){
					 $attachment = "<br> Attachment {$attachment}";
				}
				$attachment .= "</td>";
                echo "<td>{$payload}</td>";
                echo "<td>{$returnval}</td>";
                echo "<td>" . $testresult[0] . "</td>";
				if($returnval == $testresult[0]){
					echo "<td></td>";
				}else{
					echo "<td style='text-align:center;'>";
					// add the record id here
					echo "<div class='userId'>{$id}</div>";
					echo "<div class='newReturn'>" . $testresult[0] . "</div>";
					
                    //we will use this links on next part of this post
                    echo "<button class='acceptBtn rowButton'>Accept</button>";
				}
                echo "</td>";
                echo "<td style='text-align:center;'>";
					// add the record id here
					echo "<div class='buttonHolder'>";
					echo "<div class='userId'>{$id}</div>";
					
                    //we will use this links on next part of this post
                    echo "<button class='editBtn rowButton'>Edit</button>";
					
                    //we will use this links on next part of this post
                    echo "<button class='deleteBtn rowButton'>Delete</button>";
					echo "</div>";
                echo "</td>";
            echo "</tr>";
			}
        }
        
    echo "</table>";//end table
    
}

// tell the user if no records found
else{
    echo "<div class='noneFound'>No records found.</div>";
}
}

?>