<?php
$app->get('/items/create/bookmarklet', function ($request, $response, $args) {
            $db = new DbItems();
			$vars = array();
			$vars["title"] = $request->getQueryParam("title");
			$vars["description"] = $request->getQueryParam("description");
			$vars["price"] = $request->getQueryParam("price");
			$vars["url"] = $request->getQueryParam("url");
			$vars["img"] = $request->getQueryParam("img");
            // fetch item
            //$result = $db->item_get(0, $item_id);		
			//$result = array("title" => "SOME TITLE", "description" => "SOME DESCRIPTION", "image" => "default.jpg");
			  return  $this->view->render($response,"create_extension.php",$vars);

            /*if ($result != NULL) {
                $result["error"] = 0;				
                $result["images"] = array();
            	//$images = $db->images_get($item_id);
				$images = explode(",",$result["image"]);
				if (count($images) == 0){
            		$result = array();
					$result["error"] = 1;
					$result["message"] = "Sorry, an unknown error occurred.";
					//echoResponse(404,$result);
					return;
				}
				$result["images"] = $images;
				//$result["image"] = $images[0];
				
            } else {
            	$result = array();
				$result["error"] = 1;
				$result["message"] = "Sorry, this item no longer exists.";
				//echoResponse(404,$result);
				return;
            }
			//echoResponse(200,$result);*/
        });
		
		$app->post('/items/create/bookmarklet', function ($request, $response, $args) {
            $db = new DbItems();
			$vars = array();
			$vars["title"] = $request->getQueryParam("title");
			$vars["description"] =$request->getQueryParam("description");
			$vars["price"] = $request->getQueryParam("price");
			$vars["url"] = $request->getQueryParam("url");
			$vars["img"] = $request->getQueryParam("img");
			$vars["quantity"] = $request->getQueryParam("quantity");
            // fetch item
			$result = $db->items_post_from_url(1, $response,$vars["title"],$vars["price"],$vars["description"],$vars["quantity"],$vars["img"],1,1, 0);	
			//$result = array("title" => "SOME TITLE", "description" => "SOME DESCRIPTION", "image" => "default.jpg");
			echo $result;

            /*if ($result != NULL) {
                $result["error"] = 0;				
                $result["images"] = array();
            	//$images = $db->images_get($item_id);
				$images = explode(",",$result["image"]);
				if (count($images) == 0){
            		$result = array();
					$result["error"] = 1;
					$result["message"] = "Sorry, an unknown error occurred.";
					//echoResponse(404,$result);
					return;
				}
				$result["images"] = $images;
				//$result["image"] = $images[0];
				
            } else {
            	$result = array();
				$result["error"] = 1;
				$result["message"] = "Sorry, this item no longer exists.";
				//echoResponse(404,$result);
				return;
            }
			//echoResponse(200,$result);*/
        });
$app->get('/items/{id}', function ($request, $response, $args) {
            $db = new DbItems();
           $item_id=(int)$args['id'];
            // fetch item
            $result = $db->item_get(0, $item_id);		
			//$result = array("title" => "SOME TITLE", "description" => "SOME DESCRIPTION", "image" => "default.jpg");
			$this->view->render($response, "item.php",$result);
            if ($result != NULL) {
                $result["error"] = 0;				
                $result["images"] = array();
            	//$images = $db->images_get($item_id);
				$images = explode(",",$result["image"]);
				if (count($images) == 0){
            		$result = array();
					$result["error"] = 1;
					$result["message"] = "Sorry, an unknown error occurred.";
					//echoResponse(404,$result);
					return;
				}
				$result["images"] = $images;
				//$result["image"] = $images[0];				
            } else {
            	$result = array();
				$result["error"] = 1;
				$result["message"] = "Sorry, this item no longer exists.";
				//echoResponse(404,$result);
				return;
            }
			//echoResponse(200,$result);
        });
$app->get('/items', function ($request, $response, $args) {
            $db = new DbItems();
            $q = $request->getQueryParam('q');
            $newerthan_id = $request->getQueryParam('newerthan_id');
            $olderthan_id = $request->getQueryParam('olderthan_id');
            $count = $request->getQueryParam('count');
            $test = $request->getQueryParam('test');
            // fetch item
            $result = array("items"=>$db->getAllItems(0, "", $q, $newerthan_id, $olderthan_id, $count,$test));	
			//$result = array("title" => "SOME TITLE", "description" => "SOME DESCRIPTION", "image" => "default.jpg");
			$this->view->render($response,"explore.php",$result);
        });

$app->get('/', function ($request, $response, $args) {
   return $this->view->render($response, 'main.html');
});
?>