<?PHP
$imgpath = $_GET["imgpath"];
$col = $_GET["col"];
$ini = $_GET["ini"];
$type = $_GET["type"];
$width = $_GET["w"];
$height = $_GET["h"];
  if(!isset($imgpath) || !$imgpath || strpos($imgpath,"?") !== false) exit;
  if(!isset($type) || !$type) exit;
	if ($width == 0) $width = 640;
	if ($height == 0) $height = 640;
  if(!isset($width) || !$width = intval($width)) exit;
  if(!isset($height) || !$height = intval($height)) exit;
	if ($width != $height){
		exit;
	}
  
  if ($imgpath == "default"){
  	if(!isset($col) || !$col) exit;
  	if(!isset($ini) || !$ini) exit;
	$col = strtolower($col);
	$ini = strtoupper($ini);
	$sw = $width * 0.75;
	$sh = $height * 0.75;
	$ps = round($height*0.45);
  	$newimg = "tmp/defaultprofile/" . $col . "_" . $ini . "_" . $width . "_" . $height . ".jpg";
	$qry = "convert -size '{$sw}x{$sh}>' -pointsize " . $ps . " -background 'none' -fill white -gravity center label:" . $ini. " -pointsize " . $ps . " -trim +repage -gravity center -background '#" . $col . "' -extent '{$width}x{$height}>' $newimg";
	//$qry = "convert -size '{$sw}x{$sh}>' -pointsize 10 -gravity center label:" . $ini. " $newimg";
	if(!file_exists($newimg)) {
	exec($qry);
	}
				
  header("Content-type: image/jpeg");
  readfile($newimg);
  return;
  }

$CACHEPATH = "tmp";

  $oldimg = "images/uploads/" . $type . "/" . $imgpath . ".jpg";
  $newimg = "tmp/" . $type . "/" . $imgpath . "_" . $width . "_" . $height . ".jpg";

			  if(!file_exists($newimg) || (filemtime($oldimg) > filemtime($newimg))) {
				exec("convert -geometry '{$width}x{$height}>' $oldimg $newimg");	
			  }

  header("Content-type: image/jpeg");
  readfile($newimg);
?>