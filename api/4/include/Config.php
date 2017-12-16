<?php
/**
 * Database configuration
 */

// Error Messages
define('OK', 0);
define('USER_CREATE_FAILED', 1);
define('EMAIL_IN_USE', 2);
define('USERNAME_IN_USE', 3);


// Item Status
define('IMAGES_PENDING',1);
define('DELETED',2);
define('REPORTED',3);
define('PAYPAL_CLIENT_ID', 'AXvhNveDpy-sDKBVwTKLSMsU9qdjpFn3DtC0__cFElGyj2C91xxFPuGucM9qQ9Qhat6ZZdFpPGCWu5zY'); // Paypal client id
define('PAYPAL_SECRET', 'EOfIK16PvoHQtRXVzLBSHh4N04jS1s0C7-U4RcWSh_Qct19wzD8eBFpbXeWcjIvE5wczo8uY5izAuhbK'); // Paypal secret

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'jimbo');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', 3306);
?>
