<?php

$app->get('/items/:id', function($item_id) use ($app) {
            $db = new DbItems();

            // fetch item
            $result = $db->item_get(0, $item_id);		
			//$result = array("title" => "SOME TITLE", "description" => "SOME DESCRIPTION", "image" => "default.jpg");
			$app->render("item.php",$result);

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


$app->get('/', function() use ($app) {			
	$app->render("main.html");
        });
$app->get('/', function() use ($app) {			
	$app->render("main.html");
        });
?>