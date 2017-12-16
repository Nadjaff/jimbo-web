<?php
$endpoint = 'https://api.ebay.com/ws/api.dll';
$requestBody1 = '<?xml version="1.0" encoding="utf-8" ?>';
$requestBody1 .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
$requestBody1 .= "<RequesterCredentials><eBayAuthToken>AgAAAA**AQAAAA**aAAAAA**18PhVQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wNmYWoDpKBpwSdj6x9nY+seQ**CPkCAA**AAMAAA**WYaESyaaLofq60Fg/FKe7l4Wu02BdLudKYR1LtPteJ6TEz4MC2+D+SqTa2hI4RT1mYJGc4cYkfj9JBbTorGyEQ7LiUG3yfMZJHjNZyP2XQx1CA3YVk28/gM7YJqOx34UQULneKliGz6tApC5/JYOHv32geGG33ocS01xiUekDNjdvV1ikINtSNlMLaVZIz0z98aYM/i3n+gdJTyYR4CGKHrwqD2O4jhE2EgAUQKsedO/8hEEIxLtnWbcvQztiiIoGE7PkxlsJimuAz6AgF9HKy/F9NjAd8GbNJXLf3pi9+K5sF4sE40YoncT7+FsKY9BZW2cfgHHtvuIwKY6n/g+VAyCyFRPkJDuExY6DY/OpvvDMGtZh1wP0PuB28Kq0HVo0+eWA+bVHoX9T1AKtSpv3BvT88avdfhtkrn9xUhktQUGaSkbo0aZtyLSXp1rZcGlrCRey6OHf4KzzajqbivtVFWQuxjGxciE/FWO3TJ614C3Lb95JDCtOczyHWNlGC9d756/q7SRZFqicuOonoyOqSB3a4pYlLKE+7GzDaT4SVF3KM1WJNOaXQCj7ReO2Kgd1c9ef5sbNdd+imXGm0xRdH5LdIVxnwIuMrBg8rh3hRpKE8mNWInfLF2+ndiqvI4VQbEH7GoA/bYuV6T089fjlrGQ/QjJTA6MfkqlVeK339JtrieqgSSidhCeq6hayn1NOZH0SX/q1p+v+WwU8/pgBZzoD2G96/v4jqucuHBxGRDZ43Ypf8uGl21iTmjCWlOz</eBayAuthToken></RequesterCredentials>";
$requestBody1 .= "<CategoryParent>" . $_GET["p"] . "</CategoryParent><CategorySiteID>0</CategorySiteID><DetailLevel>ReturnAll</DetailLevel><LevelLimit>" . $_GET["l"] . "</LevelLimit>";
$requestBody1 .= '</GetCategoriesRequest>';

    $session  = curl_init($endpoint);                       // create a curl session

    curl_setopt($session, CURLOPT_POST, true);              // POST request type
    curl_setopt($session, CURLOPT_POSTFIELDS, $requestBody1); // set the body of the POST
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);    // return values as a string - not to std out
    $headers = array(
      'X-EBAY-API-CALL-NAME: GetCategories',
      'X-EBAY-API-SITEID: 3',                                 // Site 0 is for US
      'X-EBAY-API-APP-ID: stephen2-3a03-4682-9c18-43d386b81cbf',
      'X-EBAY-API-COMPATIBILITY-LEVEL: 515',
      "X-EBAY-API-REQUEST-ENCODING: XML",    // for a POST request, the response by default is in the same format as the request
      'Content-Type: text/xml;charset=utf-8',
    );
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);    //set headers using the above array of headers

    $responseXML = curl_exec($session);                     // send the request
    curl_close($session);
	$xml = simplexml_load_string($responseXML);
	foreach($xml->CategoryArray->Category as $cat){
		if ($cat->Expired == "false"){
			echo $cat->CategoryName;
		}
	}
    //print_r( $responseXML);  // returns a string
	?>
<?php 