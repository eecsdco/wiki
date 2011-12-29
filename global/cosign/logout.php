<?php

// set the redirect location

if ( isset($_REQUEST["redirect"]) ) $redirect = $_REQUEST["redirect"];
else $redirect = "http://www.eecs.umich.edu/";

$central = "https://weblogin.umich.edu/cosign-bin/logout?".$redirect;

// delete user session
session_start();
session_unset();
session_destroy();
// clear service cookies
setcookie( $_SERVER['COSIGN_SERVICE'], "null", time()-1, '/', "", 1 );
setcookie( $_SERVER["COSIGN_SERVICE"], "null", time()-3600 );
setcookie( $_SERVER["COSIGN_SERVICE"], "null", time()-3600, "/", "" );

// redirect to central logout page
header( "Location: $central" );
exit;
	
?>