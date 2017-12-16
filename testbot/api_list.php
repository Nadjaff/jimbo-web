<?php
//include database connection
include 'libs/db_connect.php';
include "runtest.php";

//select all data
$query = "SELECT id, url, GROUP_CONCAT(DISTINCT(method)) AS method FROM tests3 GROUP BY url ORDER BY url ASC";
$stmt = $con->prepare( $query );
$stmt->execute();

//this is how to get number of rows returned
$num = $stmt->rowCount();
echo "<li><a class='apilista'><span>Changed</span></a></li>";
echo "<li><a class='apilista'><span>All</span></a></li>";

if($num>0){ //check if more than 0 record found
	$apiList = array();
	$depths = array();
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		//extract row
		//this will make $row['firstname'] to
		//just $firstname only
		extract($row);
		if (array_search($url,$apiList) === false){
			array_push($apiList,$url);
			
			for ($j=count($depths)-1;$j>=0;$j--){
				if (strpos($url,$depths[$j]) === false){
					array_pop($depths);
				}
			}
			if(!isset($lasturl))
				$lasturl = null;
			if (strpos($url,$lasturl) !== false){
				array_push($depths,$lasturl);
			}
			echo "<li><a class='apilista apilist" . count($depths) . "'><span>{$url}</span> - ({$method})</a></li>";
			$lasturl = $url;
		}
	}
}

?>