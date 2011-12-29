<?php

// set the redirect location
if ( isset($_REQUEST["redirect"]) ) $redirect = urldecode($_REQUEST["redirect"]);
else $redirect = "https://wiki.eecs.umich.edu/";

// check the user credentials
//echo "Auth Type: ".$_SERVER["AUTH_TYPE"]."<br />";
//echo "Cosign Factor: ".$_SERVER["COSIGN_FACTOR"]."<br />";
//echo "Cosign Service: ".$_SERVER["COSIGN_SERVICE"]."<br />";
//echo "Remote User: ".$_SERVER["REMOTE_USER"]."<br />";
//exit();

// redirect the user to the requested location
header( "Location: $redirect" );
exit;

?>