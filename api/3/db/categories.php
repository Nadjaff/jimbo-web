<?php

/*$app->get('/categories', function()  use ($app){
	$db = new DbCategories();
	if ($app->request->get('update') == '1'){
		$db->loadCategories();
	}
	$t = $db->getCategories();
   echoResponse(200,$t);
});*/

$app->get('/categories', function()  use ($app){
            $db = new DbItems();
			
            $response = array();
            // fetching all user items;
            $result = $db->getCategories();
 
            $response["error"] = 0;
            $response["categories"] = $result; 

            echoResponse(200, $response);
});

$app->get('/variants', function()  use ($app){
	$db = new DbCategories();
	if ($app->request->get('update') == '1'){
		$db->loadCategories();
	}
	$t = $db->getCategories();
   echoResponse(200,$t);
});
?>	