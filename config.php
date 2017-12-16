<?php 
if ($_SERVER['HTTP_HOST'] == "jimboweb.herokuapp.com" || $_SERVER['HTTP_HOST'] == "jimbo.co") {
    // Production
    define('DB_HOST', 'tviw6wn55xwxejwj.cbetxkdyhwsb.us-east-1.rds.amazonaws.com');
	define('DB_NAME', 'upl3s5sy5ran4xg0');
	define('DB_USER', 'zog8stryj7eq5f66');
	define('DB_PASSWORD', 'qkoom9wvqs9f34w3');
	define('DB_PORT', 3306);
	if($_SERVER['HTTP_HOST'] == "jimboweb.herokuapp.com" )
	{
		define('WEB_URL', 'jimboweb.herokuapp.com');
	}else{
		define('WEB_URL', 'jimbo.co');
	}
}else {//elseif(($_SERVER['HTTP_HOST'] == "localhost") || ($_SERVER['HTTP_HOST'] == "127.0.0.1")){
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'jimbo');
	define('DB_USER', 'root');
	define('DB_PASSWORD', '');
	define('DB_PORT', 3306);
	define('WEB_URL', 'http://localhost/jimbo-web');
}

?>
