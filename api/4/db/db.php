<?php
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbBase {

    public $conn;
    public $conni;

    function __construct() {
        require_once dirname(__FILE__) . '/../include/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
        $this->conni = $db->connecti();
		//$this->getpricet = "t.currency as currency, sett.currency as currency_me, t.price / 100000.0 as price, IF (curr.name = usercurr.name, t.price / 100000.0, ROUND(t.price * 1.0 / curr.rate * usercurr.rate / 100000.0,usercurr.decimals)) as price_me";
		///$this->getpricet = "t.currency as currency, sett.currency as currency_me, t.price, IF (curr.name = usercurr.name, t.price / 1000000.0, ROUND(t.price * 1.0 / curr.rate * usercurr.rate / 1000000.0,usercurr.decimals)) as price_me";
		/*$this->getpricetablest = "
		LEFT JOIN user_settings AS sett ON sett.id = :uid		
		LEFT JOIN currencies AS curr ON curr.name = t.currency	
		LEFT JOIN currencies AS usercurr ON usercurr.name = sett.currency		";*/


		$this->getpricet = "t.currency as currency, ut.currency as currency_me, t.price, IF (curr.name = usercurr.name, t.price / 1000000.0, ROUND(t.price * 1.0 / curr.rate * usercurr.rate / 1000000.0,usercurr.decimals)) as price_me";
		$this->getpricetablest = "	
		LEFT JOIN currencies AS curr ON curr.name = t.currency	
		LEFT JOIN currencies AS usercurr ON usercurr.name = ut.currency		";
    }
	public function prepare($qry){
		print $qry;
		print preg_replace('/([a-zA-]+)\.price([ ,])/', "$1.price / 100000.0 * $1.currency AS price$2", $qry);
		return $this->conn->prepare("$qry");
	}
	
	public function limitQuery($qry,$newerthan_id,$olderthan_id,$count){
		$r = "";
		if ($count == NULL) $count = 20;
		if ($count == 0) $count = 20;
		if ($count > 100) $count = 100;
		
			
	if ($newerthan_id == 0 && $olderthan_id == 0){
			$qry = preg_replace("/WHERE\s\S*\s###\s\S*\sAND/i","WHERE",$qry);
			$qry = preg_replace("/WHERE\s\S*\s###\s\S*/i","",$qry);
			// Remove limiting on newerthan_id or olderthan_id
		}else{
			if ($newerthan_id != 0){
				$r=">";
				$rid=$newerthan_id;
			}else if ($olderthan_id != 0){
				$r="<";
				$rid=$olderthan_id;
			}
			$qry = str_replace("###",$r,$qry);
		}
	
		if (strpos($qry,"notifications") != false){
		//echo $qry;
		}
		$stmt = $this->conn->prepare("$qry");
		if ($r != "") {
		$ridc = intval($rid);
		$stmt->bindParam(':limitid', $ridc);
		}
		$cc = intval($count);

	   $stmt->bindParam(':count', $cc, PDO::PARAM_INT);
		return $stmt;
	}
	public function limitLargeQuery($qry,$newerthan_id,$olderthan_id,$count){
		$r = "";
		if ($count == 0) $count = 20;
		if ($count > 500) $count = 500;
		
		if ($newerthan_id == 0 && $olderthan_id == 0){
			$qry = preg_replace("/WHERE\s\S*\s###\s\S*\sAND/i","WHERE",$qry);
			$qry = preg_replace("/WHERE\s\S*\s###\s\S*/i","",$qry);
			// Remove limiting on newerthan_id or olderthan_id
		}else{
			if ($newerthan_id != 0){
				$r=">";
				$rid=$newerthan_id;
			}else if ($olderthan_id != 0){
				$r="<";
				$rid=$olderthan_id;
			}
			$qry = str_replace("###",$r,$qry);
		}
		if (strpos($qry,"notifications") != false){
		//echo $qry;
		}
		$stmt = $this->conn->prepare("$qry");
		if ($r != "") $stmt->bindParam(':limitid', intval($rid),PDO::PARAM_INT);
		$stmt->bindParam(':count', intval($count),PDO::PARAM_INT);
		return $stmt;
	}
	
	public function xExecute($stmt){
		if ($stmt->execute()) {
			return array("users"=>$stmt->fetchAll(PDO::FETCH_ASSOC), "error"=>0);
		}else{
			print_r("error");				
			print_r($stmt->errorInfo());
			echo $stmt->errorCode();
			return array("users"=>$stmt->fetchAll(PDO::FETCH_ASSOC), "error"=>1);
		}
	}
	public function yExecute($stmt,$n){
		if ($stmt->execute()) {
			return array($n=>$stmt->fetchAll(PDO::FETCH_ASSOC), "error"=>0);
		}else{
			print_r("error");		
			print_r($stmt->errorInfo());
			echo $stmt->errorCode();
			return array($n=>$stmt->fetchAll(PDO::FETCH_ASSOC), "error"=>1);
		}
	}
	public function singleExecute($stmt){
		if ($stmt->execute()) {
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			$res["error"] = 0;
		}else{
			print_r("error");
			print_r($stmt->errorInfo());
			echo $stmt->errorCode();
			$res = array("error"=>1);
		
		}
		return $res;
	}
	public function limitExecute($stmt){
		if ($stmt->execute()) {
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$res["error"] = 0;
		}else{
			print_r("error");
			print_r($stmt->errorInfo());
			echo $stmt->errorCode();
			$res = array("error"=>1);
		}
		return $res;
	}
}
?>
