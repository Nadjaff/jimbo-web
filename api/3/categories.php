<?php

$app->get('/categories', function()  use ($app){
	$db = new DbCategories();
	if ($app->request->get('update') == '1'){
		$db->loadCategories();
	}
	$t = $db->getCategories();
   echoResponse(200,$t);
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