<?php
/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
$app->post('/items', 'authenticate', function() use ($app) {
	
			// Create an item from whatever data we have
			
            // check for required params
            verifyRequiredParams(array('title','price','quantity','images','location_id'));

            $response = array();
            $title = $app->request->post('title');
            $price = $app->request->post('price');
            $description = $app->request->post('description');
            $quantity = $app->request->post('quantity');
            $images = $app->request->post('images');
            $status = $app->request->post('status');
            $location_id = $app->request->post('location_id');
			
			$images = explode(",",$images);

            global $current_user_id;
            $db = new DbHandler();
			$image1 = $db->getImage($current_user_id,(int)$images[0]);
			if ($image1["i"] == -1){
				throw new DataException("Image unavailable. Please try again.");
			}
			if ($image1["item_id"] != 0){
				throw new DataException("Image unavailable. Please try again.");
			}
			if ($status == NULL || $status == ""){
				$status = 1;
			}
			if ($description == NULL){
				$description = 1;
			}
			$success = true;
			if ($images != NULL){
				$item_id = $db->createItem($current_user_id, $title,$price,$description,$quantity,$image1["src"], $status,$location_id); // Note that if status is 1, this will be ignored until published.
				if ($item_id != NULL) {
					$count_images = count($images);
					for($i=0;$i<$count_images;$i++){
						$success = $db->associateImageWithItem($current_user_id, $item_id, $images[$i],$i+1);
						if ($success == false){
							break;
						}
					}
					if ($status == 1 && $success != false){
						$published = $db->publishItem($current_user_id, $item_id);
					}
				}
			}
            if ($item_id != NULL && ($published == TRUE || $status != 1) && $success == true) {
                $response["error"] = 0;
                $response["item_id"] = $item_id;
                echoResponse(201, $response);
            } else {
				if ($item_id != NULL){
					$db->deleteItem($current_user_id, $item_id);
					throw new DataException("Failed to create item. Please try again.");
				}else{
					throw new DataException("Failed to create item. Please try again.");
				}
            }
        });





/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
$app->post('/items/:id/stars', 'authenticate', function($item_id) use ($app) {
	
			// Create an item from whatever data we have
			
            // check for required params
            verifyRequiredParams(array('value'));

            $response = array();
            $val = $app->request->post('value');
            global $current_user_id;
            $db = new DbHandler(); //$db->starItem($current_user_id,$item_id,$val);
			if ($db->starItem($current_user_id,$item_id,$val) == 0){
				throw new DataException("Could not register like. Please try again.");
			}
			$response["error"] = 0;
			echoResponse(200, $response);
			return;
        });
$app->get('/items/:id/stars', 'authenticate', function($item_id) use ($app) {
	
			// Create an item from whatever data we have
			
            // check for required params
            verifyRequiredParams(array('value'));

            $response = array();
            $val = $app->request->post('value');
            global $current_user_id;
            $db = new DbHandler();
			if ($db->starItem($current_user_id,$item_id,$val) == 0){
				throw new DataException("Could not register like. Please try again.");
			}
			$response["error"] = 0;
			echoResponse(200, $response);
			return;
        });
		
		
		
		
		
		
/**
 * Listing all items of particual user
 * method GET
 * url /items          
 */
$app->get('/items', 'authenticate', function()  use ($app){
            global $current_user_id;
            $response = array();
            $db = new DbHandler();
            $q = $app->request->get('q');
            $since_id = $app->request->get('since_id');
            $max_id = $app->request->get('max_id');
            $count = $app->request->get('count');

            // fetching all user items
            echo "asdf2";
            $result = $db->getAllItems($current_user_id, $q, $since_id, $max_id, $count);
            echo "asdf";
            $response["error"] = 0;
            $response["items"] = $result;

            echoResponse(200, $response);
        });

/**
 * Listing single item of particual user
 * method GET
 * url /items/:id
 * Will return 404 if the item doesn't belongs to user
 */
$app->get('/items/:id', 'authenticate', function($item_id) {
            global $current_user_id;
            $response = array();
            $db = new DbHandler();

            // fetch item
            $result = $db->getItem($item_id, $current_user_id);

            if ($result != NULL) {
                $response["error"] = 0;
                $response["id"] = $result["id"];
                $response["user_id"] = $result["user_id"];
                $response["title"] = $result["title"];
                $response["price"] = $result["price"];
                $response["description"] = $result["description"];
                $response["quantity"] = $result["quantity"];
                $response["total_images"] = $result["totalimages"];
                $response["status"] = $result["status"];
                $response["published_at"] = $result["published_at"];
                $response["created_at"] = $result["created_at"];
				
                $response["images"] = array();
            	$images = $db->getImages($item_id);
					echo $images[1]["src"];
				for ($i=0;$i<count($images);$i++){
					$response["images"][$images[$i]["i"]-1] = $images[$i]["src"];
				}
				
				throw new ExistanceException("This item does not exist.");
            } else {
				throw new ExistanceException("This item does not exist.");
            }
        });



/**
 * Updating existing item
 * method PUT
 * params item, status
 * url - /items/:id
 */
$app->put('/items/:id', 'authenticate', function($item_id) use($app) {
            // check for required params
            verifyRequiredParams(array('item'));

            global $current_user_id;            
            $item = $app->request->put('item');
			$title = $app->request->post('title');
            $price = $app->request->post('price');
            $description = $app->request->post('description');
            $quantity = $app->request->post('quantity');
            $image1 = $app->request->post('image1');
            $status = $app->request->put('status');

            $db = new DbHandler();
            $response = array();

            // updating item
            $result = $db->updateItem($current_user_id, $item_id, $item, $title, $price, $description, $quantity, $image1, $status);
            if ($result) {
                // item updated successfully
            	echoSuccess(200, "Item updated successfully");
            } else {
                // item failed to update
				throw new DataException("Item failed to update. Please try again.");
            }
        });


/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
$app->delete('/items/:id', 'authenticate', function($item_id) use($app) {
            global $current_user_id;

            $db = new DbHandler();
            $response = array();
            $result = $db->deleteItem($current_user_id, $item_id);
            if ($result) {
                // item deleted successfully
            	echoSuccess(204, "Item deleted successfully");
            } else {
				throw new DataException("Item failed to delete. Please try again.");
            }
            echoResponse(200, $response);
        });
		
?>