<?php
global $starttime;
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 

$app->get('/categories', 'authenticate', function()  use ($app){
	global $current_user_id;
	$db = new DbTest();
	// fetching all user items
	$t = ($db->users_get_all($current_user_id, $app->request->get('q'), $app->request->get('newerthan_id'), $app->request->get('olderthan_id'), $app->request->get('count')));
global $starttime;
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   // . "This page was created in ".$totaltime." seconds"
   echoResponse(200,$t);
});
?>