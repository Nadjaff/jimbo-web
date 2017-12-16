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
$app->get('/images/:id', 'authenticate', function($image_id) {
	global $current_user_id;
	$response = array();
	$db = new DbHandler();

	// fetching all user items
	//$result = $db->getAllItemImages($current_user_id);
	echoResponse(200,$db->image_get($current_user_id, $image_id));
});

/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
$app->post('/images', 'authenticate', function() use ($app) {
            // check for required params
            $response = array();

            global $current_user_id;
            $db = new DbImages();
			
			if ( isset($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name']) ) {
				$filename = 'u' . uniqid() . '.jpg';
				$test = move_and_crop_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/items/' . $filename,610,610);
				print_r( $test);
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
				}
				
			}else{
				$response["error"] = 1;
				$response["title"] = "Error";
				$response["message"] = "Failed to upload image. Please retry";
				$response["details"] = isset($_FILES['image']['error']) ? $_FILES['image']['error'] : 'image error';
				echoResponse(200, $response);
			}
        });
$app->post('/userimage', function() use ($app) {
            // check for required params
            $response = array();

            global $current_user_id;
            $db = new DbImages();
			
			if ( is_uploaded_file($_FILES['image']['tmp_name']) ) {
				$filename = 'u' . uniqid() . '.jpg';
				$test = move_and_crop_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/profile/' . $filename,610,610);
				$response["error"] = 0;
				$response["image_id"] = "0";
				$response["url"] = $filename;
				echoResponse(201, $response);
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
				$response["error"] = 1;
				$response["title"] = "Error";
				$response["message"] = "Failed to upload image. Please retry";
				echoResponse(200, $response);
			}
        });
/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
$app->delete('/images/:id', 'authenticate', function($image_id) use($app) {
            global $current_user_id;

            $db = new DbHandler();
            $response = array();
            $result = $db->deleteImage($current_user_id, $image_id);
            if ($result) {
                // item deleted successfully
                $response["error"] = 0;
                $response["message"] = "Item deleted succesfully";
            } else {
                // item failed to delete
                $response["error"] = 1;
                $response["message"] = "Item failed to delete. Please try again!";
            }
            echoResponse(200, $response);
        });
?>