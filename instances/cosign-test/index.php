<?php

echo "Testing Cosign Data<br /><br />";

// check the user credentials
echo "Auth Type: ";
if (isset($_SERVER["AUTH_TYPE"])) echo $_SERVER["AUTH_TYPE"]; else echo "Not set!";
echo "<br />";
echo "Cosign Factor: ";
if (isset($_SERVER["COSIGN_FACTOR"])) echo $_SERVER["COSIGN_FACTOR"]; else echo "Not set!";
echo "<br />";
echo "Cosign Service: ";
if (isset($_SERVER["COSIGN_SERVICE"])) echo $_SERVER["COSIGN_SERVICE"]; else echo "Not set!";
echo "<br />";
echo "Remote User: ";
if (isset($_SERVER["REMOTE_USER"])) echo $_SERVER["REMOTE_USER"]; else echo "Not set!";
echo "<br />";

echo "<br />Testing Session Data<br /><br />";

session_start();
print_r($_SESSION);

?>