<?php
include ("./include/DbConnect.php");
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
	$stmt = $conn->prepare('INSERT INTO currencies (name, rate,created_at,updated_at) VALUES(:name, :value,:created_at,:updated_at) ON DUPLICATE KEY UPDATE rate=:value');
	//$stmt = $conn->prepare('UPDATE currencies SET name = ?, rate = ? WHERE name = ?');
	//$stmt->bind_param("sss", $keyName, $value, $keyName);
	$stmt->bindParam(":name", $keyName);
	$stmt->bindParam(":value", $value);
    $stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
	$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
    $stmt->execute();
	//echo $stmt->num_rows;
	if ($stmt->rowCount() > 0){
   		/*$stmt->close();
		$stmt = $conn->prepare('INSERT INTO currencies (name, rate) VALUES (?, ?)');
		$stmt->bind_param("ss", $keyName, $value);
		$stmt->execute();*/
		$neednames = true;
	}
	unset ($stmt);
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
		$stmt = $conn->prepare('UPDATE currencies SET description = :description,updated_at=:updated_at WHERE name = :name');
		$stmt->bindParam(":name", $keyName);
		$stmt->bindParam(":description", $value);
		$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
		$stmt->execute();
	}
}

?>