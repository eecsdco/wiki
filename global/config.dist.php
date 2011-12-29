<?php

#########################################################################
# DCO WIKI SERVER GLOBAL CONIGURATION FILE
# Changes made here will affect all active wiki instances!
#########################################################################

$global_debug = TRUE;

if ( $global_debug ) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}

// array of users that should have admins access to ALL wiki instances
$admins_dco = array(

);

// array of users that should have read/write access to ALL wiki instances
$members_dco = array(

);

// array of users that should have read access to ALL wiki instances
$readers_dco = array(

);

// array of users that should be blacklisted from ALL wiki instances
$blacklist_dco = array(

);

# MAINTENANCE OPTIONS

// set to FALSE for normal operation, anything string to enable read only and give a reason
$global_read_only = FALSE;

// set a notice that displays at the top of ALL wiki instance pages (wiki formatting)
$global_notice = "";

# DATABASE OPTIONS
$global_db_type = "mysql";
$global_db_server = "localhost";
$global_db_user = "";
$global_db_password = "";




?>
