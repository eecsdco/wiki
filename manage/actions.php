<?php

$global_path = "/y/wiki/global";
require_once("$global_path/management/wiki_management.class.php");
$manager = new EECS_WIKI_MANAGEMENT();
$wikis = $manager->wiki_list_all();
// set the shortname or redirect if not set
if ( isset($_REQUEST["w"]) ) $shortname = $_REQUEST["w"];
else header("Location: https://wiki.eecs.umich.edu/manage");
// get the list of admins for this wiki
$admins_local = $manager->wiki_users_get($shortname,"admins");
global $admins_dco;
$user = force_login(array_merge($admins_local,$admins_dco));

$message = "";
$action = "";

if ( isset($_REQUEST["a"]) ) $action = $_REQUEST["a"];

if ( $action == "user_add" ) {
	if ( isset($_REQUEST["g"]) AND isset($_REQUEST["u"]) ) {
		$group = $_REQUEST["g"];
		$user = $_REQUEST["u"];
		$result = $manager->wiki_users_add($shortname,$group,$user);
		if ( $result ) $message = "Success! User $user added to $group group.";
		else $message = "Error! Unable to add $user to $group group.";
	}
	else $message = "ERROR: Missing group assignment or username!";
}

if ( $action == "user_remove" ) {
	if ( isset($_REQUEST["g"]) AND isset($_REQUEST["u"]) ) {
		$group = $_REQUEST["g"];
		$user = $_REQUEST["u"];
		$result = $manager->wiki_users_remove($shortname,$group,$user);
		if ( $result ) $message = "Success! User $user removed from $group group.";
		else $message = "Error! Unable to remove $user from $group group.";
	}
	else $message = "ERROR: Missing group assignment or username!";
}

if ( $action == "option_set" ) {
	if ( isset($_REQUEST["o"]) AND isset($_REQUEST["v"]) ) {
		$option = $_REQUEST["o"];
		$value = $_REQUEST["v"];
		$result = $manager->wiki_option_set($shortname,$option,$value);
		if ( $result ) $message = "Success! Option $option set to $value.";
		else $message = "Error! Unable to set option $option to $value.";
	}
	else $message = "Error: Missing option or value!";
}

if ( $action == "update_start" ) {
	if ( $manager->wiki_update_run($shortname,FALSE) ) {
		$message = "The current update process is now complete for wiki $shortname.";
	}
	else $message = "ERROR upgrading to current release!";
}

if ( $action == "update_start_beta" ) {
	if ( $manager->wiki_update_run($shortname,TRUE) ) {
		$message = "The beta update process is now complete for wiki $shortname.";
	}
	else $message = "ERROR upgrading to beta release!";
}

if ( $action == "backup_start" ) {
	if ( $result = $manager->wiki_backup($shortname,"manual") ) {
		$sql = str_replace("/y/wiki","",$result["sql"]);
		$data = str_replace("/y/wiki","",$result["data"]);
		$message = "The wiki $shortname has been backed up succesfully.<br />The data directory can be found <a href='$data'>here</a> and the sql can be found <a href='$sql'>here</a>.";
	}
	else $message = "ERROR!";
}

if ( $action == "wiki_delete" ) {
	if ( isset($_REQUEST["yesimsure"]) ) {
		$manager->wiki_delete($shortname);
	}
	else $message = "Are you sure you want to completely delete wiki $shortname? This cannot be undone! However, a full backup will be done just in case. Click <a href='actions.php?w=$shortname&a=wiki_delete&yesimsure=1'>here</a> to confirm.";
}

if ( $action == "wiki_create" ) {
	if ( $manager->wiki_create($shortname) ) $message = "Wiki $shortname has been created! Please check the options below. Don't forget to change the full name to something recognizable.";
	else $message = "ERROR!";
}

// send user back to management page
$message = urlencode($message);
header("Location: https://wiki.eecs.umich.edu/manage/manage.php?w=$shortname&m=$message");

?>