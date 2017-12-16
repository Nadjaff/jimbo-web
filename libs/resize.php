<?PHP
$imgpath = $_GET["imgpath"];
$id = $_GET["id"];
$type = $_GET["type"];
$width = $_GET["w"];
$height = $_GET["h"];
  if(!isset($imgpath) || !$imgpath || strpos($imgpath,"?") !== false) exit;
  if(!isset($type) || !$type) exit;
  if(!isset($width) || !$width = intval($width)) exit;
  if(!isset($height) || !$height = intval($height)) exit;
  
  if ($imgpath == "default"){
  	if(!isset($id) || !$id) exit;
	$api = 'api/3/';
	require_once $api . 'db/db.php';
	require_once $api . 'db/db_users.php';
	$db = new DbUsers();
	$r = $db->user_get_initials($id);
	$namearr = explode (" ",$r["name"]);
	if (count($namearr) > 0){
		if (strlen($namearr[0]) > 0){
			$l1 = substr($namearr[0],0,1);
		}
	}
	if (count($namearr) > 1){
		if (strlen($namearr[1]) > 0){
			$l2 = substr($namearr[1],0,1);
		}
	}
  	$newimg = "../tmp/defaultprofile/" . $r["col"] . $l1 . "_" . $l2 . "_" . $width . "_" . $height . ".jpg";
	exec("convert -size '{$width}x{$height}>' xc:#" . $r["col"] . " $newimg");	
				
  header("Content-type: image/jpeg");
  readfile($newimg);
  return;
  }

$CACHEPATH = "../tmp";

  $oldimg = "../images/uploads/" . $type . "/" . $imgpath . ".jpg";
  $newimg = "../tmp/" . $type . "/" . $imgpath . "_" . $width . "_" . $height . ".jpg";

			  if(!file_exists($newimg) || (filemtime($oldimg) > filemtime($newimg))) {
				exec("convert -geometry '{$width}x{$height}>' $oldimg $newimg");	
			  }

  header("Content-type: image/jpeg");
  readfile($newimg);
?>