<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------Categories--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
$app->get('/categories', function($request, $response, $args){
	getCategories($request, $response, $args);
})->add($public);	
$app->get('/variants', function($request, $response, $args){
	getVariants($request, $response, $args);
})->add($public);	
function getCategories($request, $response, $args){
	$db = new DbCategories();
	global $r;
	$r=array();
	
	if ($request->getQueryParam('update') == '1'){
		$db->loadCategories();
	}
	$r = $db->getCategories();
	return $response->withStatus(200);
 
}
function getVariants($request, $response, $args)
{
	$db = new DbCategories();
	global $r;
	$r=array();
	if ($request->getQueryParam('update') == '1'){
		$db->loadCategories();
	}
	$r = $db->getCategories();
   	return $response->withStatus(200);
}
?>