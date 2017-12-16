<?php
//include database connection
include 'libs/db_connect.php';
include "runtest.php";
include '../config.inc';
include 'helper/php-diff/diff.php';
$filtered = false;

if ($_REQUEST["type"] == NULL || $_REQUEST["type"] == "" || $_REQUEST["type"] == "Changed" || $_REQUEST["type"] == "All"){
	$query = "SELECT id, method, url, headers, payload, returnval, ignorekeys, attachment FROM tests" . $_GET["s"] . " ORDER BY url";
	$stmt = $con->prepare( $query );
	$stmt->execute();
	if (($_REQUEST["type"] == "Changed")){
		$changes = true;
	}else{
		$changes = false;
	}
	displayResult($baseurl . $_REQUEST["s"] . "/",$stmt,$changes);
}else{
	echo "<h1>GET</h1>";
	$query = "SELECT id, method, url, headers, payload, returnval, ignorekeys, attachment FROM tests" . $_GET["s"] . " WHERE url=:url AND method='GET'";
	$stmt = $con->prepare( $query );
	$stmt->bindParam(':url', $_REQUEST['type']);
	$stmt->execute();
	displayResult($baseurl . $_REQUEST["s"] . "/",$stmt);
	
	
	echo "<h1>POST</h1>";
	$query = "SELECT id, method, url, headers, payload, returnval, ignorekeys, attachment FROM tests" . $_GET["s"] . " WHERE url=:url AND method='POST'";
	$stmt = $con->prepare( $query );
	$stmt->bindParam(':url', $_REQUEST['type']);
	$stmt->execute();
	displayResult($baseurl . $_REQUEST["s"] . "/", $stmt);
	
	
	
	echo "<h1>UPDATE</h1>";
	$query = "SELECT id, method, url, headers, payload, returnval, ignorekeys, attachment FROM tests" . $_GET["s"] . " WHERE url=:url AND method='UPDATE'";
	$stmt = $con->prepare( $query );
	$stmt->bindParam(':url', $_REQUEST['type']);
	$stmt->execute();
	displayResult($baseurl . $_REQUEST["s"] . "/", $stmt);
	
	
	
	echo "<h1>DELETE</h1>";
	$query = "SELECT id, method, url, headers, payload, returnval, ignorekeys, attachment FROM tests" . $_GET["s"] . " WHERE url=:url AND method='DELETE'";
	$stmt = $con->prepare( $query );
	$stmt->bindParam(':url', $_REQUEST['type']);
	$stmt->execute();
	displayResult($baseurl . $_REQUEST["s"] . "/", $stmt);
	
	
}

//select all data

//this is how to get number of rows returned
function displayResult($baseurl,$stmt,$changes=false){
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
            echo "<th>Ignored Key/s</th>";
            echo "<th>Accept Change</th>";
            echo "<th style='text-align:center;'>Action</th>";
        echo "</tr>";

        $diff = new diff_class;
		$diff->insertedStyle = 'background-color: lightblue; font-size: 12px;';
		$diff->deletedStyle = 'background-color: red; font-size: 12px;';
		$difference = new stdClass;
		$difference->mode = 'v';
		$difference->patch = true;
		$after_patch = new stdClass;

	    //retrieve our table contents
        //fetch() is faster than fetchAll()
        //http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            //extract row
            //this will make $row['firstname'] to
            //just $firstname only
            extract($row);
			$testresult = runTest($baseurl,$method,$url,$headers,$payload,$attachment);
			$returnval = str_replace('\\', '', stripslashes($returnval));
			$ignorekeys = str_replace('\\', '', stripslashes($ignorekeys));
            
			if($changes == false || $returnval != $testresult[0]){
				$returnval_filtered = trim(filterIgnoreKeys($returnval, $ignorekeys));
				$testresult_filtered = trim(filterIgnoreKeys($testresult[0], $ignorekeys));
				$displayNow = false;
				if($changes && $returnval_filtered != $testresult_filtered) { // global condition to body if the chosen api is "Changed" meaning only those with changes will be visible
					$displayNow = true;
	            } elseif($changes && $returnval_filtered == $testresult_filtered){
	            	$displayNow = false;
	            } else {
	            	$displayNow = true;
	            }

	            if($displayNow) {
	            	$testresult_display = $testresult[0];
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
	                echo "<td>". ( $returnval ) . "</td>";

	                if($returnval_filtered == $testresult_filtered){
	                	echo "<td>" . ( $testresult_display ) . "</td>";
	                }
	                else {
		                if($diff->FormatDiffAsHtml($returnval, $testresult_display, $difference)
						&& $diff->Patch($returnval, $difference->difference, $after_patch))
						{
							echo "<td>" . $difference->html . "</td>";
						}
						else {
			                echo "<td>" . ( $testresult_display ) . "</td>";
			            }
		        	}
	                echo "<td>" . $ignorekeys . "</td>";
					if($returnval_filtered == $testresult_filtered){
						echo "<td></td>";
					}else{
						echo "<td style='text-align:center;'>";
						// add the record id here
						echo "<div class='userId'>{$id}</div>";
						echo "<div class='newReturn'>" . $testresult[0] . "</div>";
						
	                    //we will use this links on next part of this post
	                    echo "<button class='acceptBtn rowButton'>Accept</button>";
	                	echo "</td>";
					}
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
        }
        
    echo "</table>";//end table
    
}

// tell the user if no records found
else{
    echo "<div class='noneFound'>No records found.</div>";
}
}

function filterIgnoreKeys($val, $exclude_array=null) {
	$exclude_starting_index = 0;
	$exclude_starting_length = 0;
	$exclude_ending_index = 0;
	$strip_string = '';

	if($exclude_array === null || $exclude_array === '')
		return $val;

	$val = stripslashes($val);

	$exclude_array = explode(',', $exclude_array);

	foreach ($exclude_array as $key => $exclude) {
	
		$exclude = trim($exclude);

		$exclude_starting_index = strpos(strtolower($val), strtolower($exclude));

		if($exclude_starting_index && ($val[$exclude_starting_index - 2] == '{' || $val[$exclude_starting_index - 2] == ',') ) { // check if the value is a valid key from the json
			$exclude_starting_length = $exclude_starting_index + strlen($exclude);

			$key_semi_colon = $exclude_starting_length + 1;

			if($val[$key_semi_colon + 1] != '"')
				$is_string = false;
			else
				$is_string = true;

			if($is_string) { // We should handle string with commas (,) on them. We should make sure they are inside a quote

				$start_quote_index = strpos($val, '"', $exclude_starting_length + 1); // get the index of the first quote;
				
				$end_quote_index = strpos($val, '"', $start_quote_index + 1);
				
				if(strpos($val, ',', $end_quote_index)){
					// This means we still have multiple data ahead
					$exclude_ending_index = strpos($val, ',', $end_quote_index);
					$strip_string = substr($val, $exclude_starting_index -1 , $exclude_ending_index - $exclude_starting_index + 2);
				}
				elseif(strpos($val, '"', $end_quote_index)){
					// This means this key is the last key, we need to strip up to the last quote and remove the previous comma since we reached the last key of the json
					$exclude_ending_index = strpos($val, '"', $end_quote_index);
					$strip_string = substr($val, $exclude_starting_index -2 , $exclude_ending_index - $exclude_starting_index + 2);
				}
				
				$val = str_replace($strip_string, '', $val);
			} else {
				$exclude_ending_index = strpos($val, ',', $exclude_starting_length);

				$strip_string = substr($val, $exclude_starting_index -1 , $exclude_ending_index - $exclude_starting_index + 2);

				$val = str_replace($strip_string, '', $val);
			}
		}
	}

	return $val;
}

function prettyPrint( $json ){

	$result = '';
	$level = 0;
	$in_quotes = false;
	$in_escape = false;
	$ends_line_level = NULL;
	$json_length = strlen( $json );

	for( $i = 0; $i < $json_length; $i++ ) {
	    $char = $json[$i];
	    $new_line_level = NULL;
	    $post = "";
	    if( $ends_line_level !== NULL ) {
	        $new_line_level = $ends_line_level;
	        $ends_line_level = NULL;
	    }
	    if ( $in_escape ) {
	        $in_escape = false;
	    } else if( $char === '"' ) {
	        $in_quotes = !$in_quotes;
	    } else if( ! $in_quotes ) {
	        switch( $char ) {
	            case '}': case ']':
	                $level--;
	                $ends_line_level = NULL;
	                $new_line_level = $level;
	                $char.="<br>";
	                for($index=0;$index<$level-1;$index++){$char.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";}
	                break;

	            case '{': case '[':
	                $level++;
	                $char.="<br>";
	                for($index=0;$index<$level;$index++){$char.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";}
	                break;
	            case ',':
	                $ends_line_level = $level;
	                $char.="<br>";
	                for($index=0;$index<$level;$index++){$char.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";}
	                break;

	            case ':':
	                $post = " ";
	                break;

	            case "\t": case "\n": case "\r":
	                $char = "";
	                $ends_line_level = $new_line_level;
	                $new_line_level = NULL;
	                break;
	        }
	    } else if ( $char === '\\' ) {
	        $in_escape = true;
	    }
	    if( $new_line_level !== NULL ) {
	        $result .= "\n".str_repeat( "\t", $new_line_level );
	    }
	    $result .= $char.$post;
	}

	// echo "RESULTS ARE: <br><br>$result";
	return $result;
}
?>