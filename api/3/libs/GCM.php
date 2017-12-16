<?php
 
class GCM {
 
    //put your code here
    // constructor
    function __construct() {
        require_once dirname(__FILE__) . '/../include/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
        $this->conni = $db->connecti();
         
    }
 
    /**
     * Sending Push Notification
     */
	 public function get_regs($user_ids){
		 $stmt = $this->conn->prepare("SELECT GROUP_CONCAT(regid) as regs FROM sessions GROUP BY user_id HAVING user_id = :user_id");
	
		$stmt->bindParam(":user_id", $user_ids);
	
		$stmt->execute();
		
		if (($res = $stmt->fetch(PDO::FETCH_ASSOC)))
		return $res["regs"];
	 }
    public function send_notification($user_ids, $title, $message, $action,$obj) {
		$stmt = $this->conn->prepare("SELECT regid FROM sessions GROUP BY user_id HAVING user_id = :user_id");
	
		$stmt->bindParam(":user_id", $user_ids);
	
		$stmt->execute();
		$regs = array();
		while (($res = $stmt->fetch(PDO::FETCH_ASSOC))){
			array_push($regs,$res["regid"]);
		}
		if (count($regs) > 0){
			//echo $res["regs"];
			$da = array("title" => $title, "message" => $message, "action" => $action, "data" => $obj);
			//print_r( $action);
			$this->send_to_reg( $regs,$da);
		}
	}
    public function send_notificationAll($title, $message, $action,$obj) {
		$stmt = $this->conn->prepare("SELECT regid FROM sessions");			
	
		$stmt->execute();
		$regs = array();
		while (($res = $stmt->fetch(PDO::FETCH_ASSOC))){
			array_push($regs,$res["regid"]);
		}
		if (count($regs) > 0){
			//echo $res["regs"];
			$da = array("title" => $title, "message" => $message, "action" => $action, "data" => $obj);
			//print_r( $action);
			$this->send_to_reg( $regs,$da);
		}
	}
	require( "OneSignal/OneSignal.php");
	use OneSignal\Config;
	use OneSignal\Devices;
	use OneSignal\OneSignal;
    public function send_to_reg_new($registatoin_ids, $message) {
		$config = new Config();
		$config->setApplicationId('734f9d6c-970e-406c-91cd-7e33b4ef91ff');
		$config->setApplicationAuthKey('ZDJlOTY0ZTktYTM2NS00ZjY2LTk4MjEtZDEwMWRkNjA0NWRh');
		
		$api = new OneSignal($config);
		$api->notifications->add([
			'contents' => [
				'en' => $message["message"],
			],
			'included_segments' => ['All'],
			'data' => $message,
			'include_player_ids' => $registatoin_ids
		]));
	}
	public function send_to_reg($registatoin_ids, $message) {
        // include config
        define("GOOGLE_API_KEY", "AIzaSyD2PIYZaYcetlrVu-9YQjWw0KXY06pDu9c"); // Place your Google API Key
 
        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';
 
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );
		/*echo "<br>";
		print_r($fields);
		echo "<br>";*/
        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();
 
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
 
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
 
        // Close connection
        curl_close($ch);
        //echo $result;
    }
 
}
 
?>