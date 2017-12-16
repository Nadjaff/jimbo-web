<?php
/**
 * Listing all items of particual user
 * method GET
 * url /items          
 */
/*$app->get('/images/:id', 'authenticate', function($item_id) {
            global $current_user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user items
            //$result = $db->getAllItemImages($current_user_id);
			echoResponse(200,$db->image_get($current_user_id, $item_id));
        });*/
$app->post('/userimage',  function ($request, $response, $args) {
	postUserImage($request, $response, $args,"");
})->add($public);

$app->get('/images/{id}',  function ($request, $response, $args) {
	getImage($request, $response, $args,"");
})->add($authentication);

$app->post('/images',  function ($request, $response, $args) {
	postImage($request, $response, $args,"");
})->add($authentication);

$app->delete('/images/{id}',  function ($request, $response, $args) {
	deleteImage($request, $response, $args,"");
})->add($authentication);




function getImage($request, $response, $args,$filter){
//$app->get('/images/:id', 'authenticate', function($image_id) {
	global $current_user_id;
	global $r;
	$r = array();
	$db = new DbHandler();
	$image_id = (int)$args['id'];
	
	// fetching all user items
	//$result = $db->getAllItemImages($current_user_id);
	$r = ($db->image_get($current_user_id, $image_id));

    return $response->withStatus(200);
}

/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
function postImage($request, $response, $args,$filter){
//$app->post('/images', 'authenticate', function() use ($app) {
            // check for required params
	//		echo "asdf";
			global $r;
            $r = array();

            global $current_user_id;
            $db = new DbImages();
			
			//echo "asdf";
			//echo isset($_FILES['image']['tmp_name']);
         //   echo "asaddfdf";
            global $log;
    $uploadedFiles = $request->getUploadedFiles();

            $log[] = isset($_FILES['image']['tmp_name']);
            $log[] = $uploadedFiles;
			if ( isset($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name']) ) {
				$filename = 'u' . uniqid() . '.jpg';				
				$test = move_and_crop_uploaded_file($_FILES['image']['tmp_name'], dirname(dirname(dirname(dirname(__FILE__)))).'/images/uploads/items/' . $filename,610,610);
				
				//move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/items/' . $filename);
				// creating new item
				$image_id = $db->createImage($current_user_id,$filename);		
				if ($image_id == null) {					 
				    $r["error"] = 1;
					$r["title"] = "Error";
					$r["message"] = "Failed to create image. Please try again";
                   return $response->withStatus(200);
				} else {
					$r["error"] = 0;
					$r["image_id"] = $image_id;
					$r["url"] = $filename;
                   return $response->withStatus(201);
				}
				
			}else{ 
				$r["error"] = 1;
				$r["title"] = "Error";
				$r["message"] = "Failed to upload image. Please retry";
				$r["details"] = isset($_FILES['image']['error']) ? $_FILES['image']['error'] : 'image error';
    return $response->withStatus(200);
			}
        }
function postUserImage($request, $response, $args,$filter){
//$app->post('/userimage', function() use ($app) {
            // check for required params			
	        global $r;
            $r = array();
            global $current_user_id;
            $db = new DbImages();
			if ( is_uploaded_file($_FILES['image']['tmp_name']) ) {
				$filename = 'u' . uniqid() . '.jpg';
				$test = move_and_crop_uploaded_file($_FILES['image']['tmp_name'], dirname(dirname(dirname(dirname(__FILE__)))).'/images/uploads/profile/' . $filename,610,610);
               
			   $r["error"] = 0;
				$r["image_id"] = "0";
				$r["url"] = $filename;
    			return $response->withStatus(201);
				//echoResponse(201, $response);
				/*print_r( $test);
				//move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/items/' . $filename);
				// creating new item
				$image_id = $db->createImage($current_user_id,$filename);
				if ($image_id != NULL) {
					$response["error"] = 0;
					$response["image_id"] = $image_id;
					$response["url"] = $filename;
					echoResponse(201, $response);
				} else {
					$response["error"] = 1;
					$response["title"] = "Error";
					$response["message"] = "Failed to create image. Please try again";
					echoResponse(200, $response);
				}*/
				
			}else{
				$r["error"] = 1;
				$r["title"] = "Error";
				$r["message"] = "Failed to upload image. Please retry";
    			return $response->withStatus(200);
			}
        }
/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
function deleteImage($request, $response, $args,$filter){
//$app->delete('/images/:id', 'authenticate', function($image_id) use($app) {
            global $current_user_id;
            global $r;
            $image_id = (int)$args['id'];
            $db = new DbHandler();
            $r = array();
            $result = $db->deleteImage($current_user_id, $image_id);
            if ($result) {
                // item deleted successfully
                $r["error"] = 0;
                $r["message"] = "Item deleted succesfully";
            } else {
                // item failed to delete
                $r["error"] = 1;
                $r["message"] = "Item failed to delete. Please try again!";
            }
			return $response->withStatus(200);
        }
	?>