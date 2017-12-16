<?php
function uploadProfilePic($src, $current_user_id){
	$filename = "";
	if ( is_uploaded_file($src) ) {
		$filename =  'u' . uniqid() . '.jpg';
		move_and_crop_uploaded_file($src, $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/profile/'. $filename,610,610);
	}
	return $filename;
}

function move_and_crop_uploaded_file($cropfile,$location,$new_w,$new_h){
        $image_info = getimagesize($cropfile);
		try {
			switch($image_info["mime"]){
				case "image/jpeg":
					$source_img = imagecreatefromjpeg($cropfile); //jpeg file
				break;
				case "image/gif":
					$source_img = imagecreatefromgif($cropfile); //gif file
			  break;
			  case "image/png":
				  $source_img = imagecreatefrompng($cropfile); //png file
			  break;
			}
		} catch(Exception $e){
			return "failed to create image";
			//print_r("failed to create");
		}
        if (!$source_img) {
		return "could not create image handle";
           // echo "could not create image handle";
            exit(0);
        }
        // set your width and height for the thumbnail
		move_and_crop_image($source_img,$location,$new_w,$new_h);
}
function move_and_crop_image($source_img, $location, $new_w, $new_h){

        $orig_w = imagesx($source_img);
        $orig_h = imagesy($source_img);

        $w_ratio = ($new_w / $orig_w);
        $h_ratio = ($new_h / $orig_h);

        if ($orig_w > $orig_h ) {//landscape from here new
            $crop_w = round($orig_w * $h_ratio);
            $crop_h = $new_h;
            $src_x = ceil( ( $orig_w - $orig_h ) / 2 );
            $src_y = 0;
        } elseif ($orig_w < $orig_h ) {//portrait
            $crop_h = round($orig_h * $w_ratio);
            $crop_w = $new_w;
            $src_x = 0;
            $src_y = ceil( ( $orig_h - $orig_w ) / 2 );
        } else {//square
            $crop_w = $new_w;
            $crop_h = $new_h;
            $src_x = 0;
            $src_y = 0;
        }
        $dest_img = imagecreatetruecolor($new_w,$new_h);
        imagecopyresampled($dest_img, $source_img, 0 , 0 , $src_x, $src_y, $crop_w, $crop_h, $orig_w, $orig_h); //till here
        if(imagejpeg($dest_img, $location)) {
            imagedestroy($dest_img);
            imagedestroy($source_img);
        } else {
			
            return "could not make thumbnail image";
            exit(0);
        }
}

function move_crop_and_return_image($source_img, $location, $new_w, $new_h, $dir){
        if (!file_exists($dir)) {
          mkdir($dir, 0777, true);
        }
        $orig_w = imagesx($source_img);
        $orig_h = imagesy($source_img);

        $w_ratio = ($new_w / $orig_w);
        $h_ratio = ($new_h / $orig_h);

        if ($orig_w > $orig_h ) {//landscape from here new
            $crop_w = round($orig_w * $h_ratio);
            $crop_h = $new_h;
            $src_x = ceil( ( $orig_w - $orig_h ) / 2 );
            $src_y = 0;
        } elseif ($orig_w < $orig_h ) {//portrait
            $crop_h = round($orig_h * $w_ratio);
            $crop_w = $new_w;
            $src_x = 0;
            $src_y = ceil( ( $orig_h - $orig_w ) / 2 );
        } else {//square
            $crop_w = $new_w;
            $crop_h = $new_h;
            $src_x = 0;
            $src_y = 0;
        }
        $dest_img = imagecreatetruecolor($new_w,$new_h);
        imagecopyresampled($dest_img, $source_img, 0 , 0 , $src_x, $src_y, $crop_w, $crop_h, $orig_w, $orig_h); //till here
        if(imagejpeg($dest_img, $location, 100)) {
            imagedestroy($dest_img);
            imagedestroy($source_img);
            return 1;
        } else {
            
            die("could not make thumbnail image");
            // exit(0);
        }
}

?>