<?php
include ("../include/DbConnect.php");
$db = new DbConnect();
$conn = $db->connect();


// Requested file
// Could also be e.g. 'currencies.json' or 'historical/2011-01-01.json'
$file = 'latest.json';
$appId = '4fcec4dfc681400e962ba5f9a71530d2';

// Open CURL session:
$ch = curl_init("http://openexchangerates.org/api/{$file}?app_id={$appId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Get the data:
$json = curl_exec($ch);
curl_close($ch);


// Decode JSON response:
$exchangeRates = json_decode($json,true);
$json = $exchangeRates["rates"];
//echo var_dump($json);
$neednames = false;
foreach($json as $keyName => $value) {
	$stmt = $conn->prepare('INSERT INTO currencies (name, rate) VALUES(?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), rate=VALUES(rate)');
	//$stmt = $conn->prepare('UPDATE currencies SET name = ?, rate = ? WHERE name = ?');
	//$stmt->bind_param("sss", $keyName, $value, $keyName);
	$stmt->bind_param("ss", $keyName, $value);
    $stmt->execute();
	//echo $stmt->num_rows;
	if ($stmt->affected_rows > 0){
   		/*$stmt->close();
		$stmt = $conn->prepare('INSERT INTO currencies (name, rate) VALUES (?, ?)');
		$stmt->bind_param("ss", $keyName, $value);
		$stmt->execute();*/
		$neednames = true;
	}
    $stmt->close();
}
if ($neednames == true){
	// Requested file
	// Could also be e.g. 'currencies.json' or 'historical/2011-01-01.json'
	$file = 'currencies.json';
	$appId = '4fcec4dfc681400e962ba5f9a71530d2';
	
	// Open CURL session:
	$ch = curl_init("http://openexchangerates.org/api/{$file}?app_id={$appId}");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	// Get the data:
	$json = curl_exec($ch);
	curl_close($ch);
	
	$exchangeRates = json_decode($json,true);
	foreach($exchangeRates as $keyName => $value) {
		$stmt = $conn->prepare('UPDATE currencies SET description = ? WHERE name = ?');
		$stmt->bind_param("ss", $value, $keyName);
		$stmt->execute();
	}
}

?>