<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada	
 * @link URL Tutorial link
 */
class DbCategories extends DbBase {
	
	public function loadCategories() {
		
		$endpoint = 'https://api.ebay.com/ws/api.dll';
		$requestBody1 = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestBody1 .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestBody1 .= "<RequesterCredentials><eBayAuthToken>AgAAAA**AQAAAA**aAAAAA**18PhVQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wNmYWoDpKBpwSdj6x9nY+seQ**CPkCAA**AAMAAA**WYaESyaaLofq60Fg/FKe7l4Wu02BdLudKYR1LtPteJ6TEz4MC2+D+SqTa2hI4RT1mYJGc4cYkfj9JBbTorGyEQ7LiUG3yfMZJHjNZyP2XQx1CA3YVk28/gM7YJqOx34UQULneKliGz6tApC5/JYOHv32geGG33ocS01xiUekDNjdvV1ikINtSNlMLaVZIz0z98aYM/i3n+gdJTyYR4CGKHrwqD2O4jhE2EgAUQKsedO/8hEEIxLtnWbcvQztiiIoGE7PkxlsJimuAz6AgF9HKy/F9NjAd8GbNJXLf3pi9+K5sF4sE40YoncT7+FsKY9BZW2cfgHHtvuIwKY6n/g+VAyCyFRPkJDuExY6DY/OpvvDMGtZh1wP0PuB28Kq0HVo0+eWA+bVHoX9T1AKtSpv3BvT88avdfhtkrn9xUhktQUGaSkbo0aZtyLSXp1rZcGlrCRey6OHf4KzzajqbivtVFWQuxjGxciE/FWO3TJ614C3Lb95JDCtOczyHWNlGC9d756/q7SRZFqicuOonoyOqSB3a4pYlLKE+7GzDaT4SVF3KM1WJNOaXQCj7ReO2Kgd1c9ef5sbNdd+imXGm0xRdH5LdIVxnwIuMrBg8rh3hRpKE8mNWInfLF2+ndiqvI4VQbEH7GoA/bYuV6T089fjlrGQ/QjJTA6MfkqlVeK339JtrieqgSSidhCeq6hayn1NOZH0SX/q1p+v+WwU8/pgBZzoD2G96/v4jqucuHBxGRDZ43Ypf8uGl21iTmjCWlOz</eBayAuthToken></RequesterCredentials>";
		if ($_GET["l"] != NULL && $_GET["l"] != ""){
			$requestBody1 .= "<CategoryParent>" . $_GET["p"] . "</CategoryParent>";
		}
		$requestBody1 .= "<CategorySiteID>0</CategorySiteID><DetailLevel>ReturnAll</DetailLevel>";
		if ($_GET["l"] != NULL && $_GET["l"] != ""){
			$requestBody1 .= "<LevelLimit>" . $_GET["l"] . "</LevelLimit>";
		}
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
					$allowfruit = ($cat->LeafCategory == "false")?0:1;
					$stmt = $this->conn->prepare("INSERT INTO branches(id, parentbranch, name, thickness, allowfruit) values(:id, :parentbranch, :name, :thickness, :allowfruit)");
					$stmt->bindParam(":id", $cat->CategoryID);
					$stmt->bindParam(":parentbranch", $cat->CategoryParentID);
					$stmt->bindParam(":name", $cat->CategoryName);
					$stmt->bindParam(":thickness", $cat->CategoryLevel);
					$stmt->bindParam(":allowfruit", $allowfruit);
					$stmt->execute();
				}
			}
	}
	
	public function max_bid_create($current_user_id, $item_id, $bid) {
		// fetching user by email
		$stmt = $this->conn->prepare("SELECT id, item_id, user_id, bid, created_at FROM max_bids WHERE item_id = :item_id AND bid >= :bid ORDER BY id DESC LIMIT 0,1");
	
		$stmt->bindParam(":item_id", $item_id);	
		$stmt->bindParam(":bid", $bid);
	
		$stmt->execute();
		if (($res = $stmt->fetch(PDO::FETCH_ASSOC)) == NULL) {
			$stmt = $this->conn->prepare("INSERT INTO max_bids(item_id, user_id, bid) values(:item_id, :user_id, :bid)");
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":item_id", $item_id);
			$stmt->bindParam(":bid", $bid);
			if ($stmt->execute()) {
				return array("error"=>"0", "message"=>"Max bid set");
			}
		} else {
			return array("error"=>"2", "message"=>"Bid must be higher than the current bid");
		}
		return array("error"=>"1", "message"=>"Error making bid");
	}
	
	
	/*public function getCategories(){
		$stmt = $this->conn->prepare("SELECT b.name, b.id, b.thickness FROM branches b WHERE b.parentbranch=0");
		//$stmt->bindParam(":item_id",$item_id);
		return $this->xExecute($stmt);
	}*/
	public function getCategories(){
		$count = 50;
		$newerthan_id = 0;
		$olderthan_id = 0;
		$stmt = $this->limitQuery("SELECT t.title, t.hashtag, t.image, t.id FROM categories t WHERE t.id ### :limitid ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		return $this->yExecute($stmt,"categories");
	}
	
	
}
?>
