<?php
// SESSIONS ERRORS
define('E_SESSIONS_AUTHENTICATION_REQUIRED', 100);
define('E_SESSIONS_INVALID_APIKEY',101);

// USERS ERRORS
define('E_USERS_POST_SQL', 1);
define('E_USERS_POST_FAILED_EXISTED', 2);
define('E_USERS_POST_FORGOT_PASSWORD', 2);
define('E_USERS_POST_CONFIRM_MOBILE', 2);
define('E_USERS_POST_FOLLOW', 2);






// $app->error(function (\Exception $e) use ($app) {

//     $mediaType = $app->request->getMediaType();
//     $isAPI = (bool) preg_match('|^/api/v.*$|', $app->request->getPath());
	
//     // Standard exception data
//     $error = array(
//         'code' => $e->getCode(),
//         'message' => $e->getMessage(),
//         'httpcode' => $e->httpcode,
//         'file' => $e->getFile(),
//         'line' => $e->getLine(),
//     );

//     // Graceful error data for production mode
//     if (!in_array(
//         get_class($e),
//         array('Slim\\Exception', 'Slim\\Exception\ValidationException', 'Slim\\Exception\LoginException')
//     )
//         && 'production' === $app->config('mode')) {
//         $error['message'] = 'There was an internal error';
//         unset($error['file'], $error['line']);
//     }

//     // Custom error data (e.g. Validations)
//     if (method_exists($e, 'getData')) {
//         $errors = $e->getData();
//     }

//     if (!empty($errors)) {
//         $error['errors'] = $errors;
//     }

//     //$log->error($e->getCode() . ": " . $e->getMessage());
//     if ('application/json' === $mediaType || true === $isAPI) {
//         $app->response->headers->set(
//             'Content-Type',
//             'application/json'
//         );
//         echo json_encode($error, JSON_PRETTY_PRINT);
//     } else {
//         echo '{"error":' . $e->getCode() . ',"message":"' . $error['message'] . '"}';
//     }

// });
?>