<?php

$global_path = "/y/wiki/global";
require_once("$global_path/management/wiki_management.class.php");
$manager = new EECS_WIKI_MANAGEMENT();
$wikis = $manager->wiki_list_all();
$user = force_login();

?>

<html>
<head>
<title>EECS Wiki Server</title>
<link rel="stylesheet" href="manage.css" type="text/css" />
</head>
<body>

<p><a href="../">back</a> | welcome <?php echo $user; ?> | <a href="/global/cosign/logout.php">logout</a></p>

<h1>EECS Wiki Server</h1>

<?php
// show another menu only to DCO staff
global $admins_dco;
if ( in_array($user,$admins_dco) ) {
?>
<div id="menu">
	<b>Create a new wiki?</b><br /><br />
	all lowercase letters or numbers, no spaces or special characters<br /><br />
	<form action="actions.php" method="get">
		shortname: <input type="text" name="w" />
		<input type="submit" value="Create Now" />
		<input type="hidden" name="a" value="wiki_create" />
	</form>
</div>
<?php } ?>

<div id="menu">The following list includes all wikis that you have been granted administrative access to.<br />Click the name to modify options, security settings, and group memberships for that wiki.</div>

<table>
<?php
	$count = 0;
	foreach ( $wikis as $wiki ) {
		if ( $manager->wiki_is_admin($wiki["shortname"],$_SERVER["REMOTE_USER"]) ) {
			if ( ($count == 0) OR (($count%3 ) == 0) ) echo "<tr>";
			echo "<td><a href='manage.php?w=".$wiki["shortname"]."'>".$wiki["fullname"]."</a></td>";
			if ( ($count+1)%3 == 0 ) echo "</tr>";
			$count = $count + 1;
		}
	}
?>
</table>

<div id="footer">&copy; <?php date_default_timezone_set("America/Detroit"); echo date("Y"); ?> <a href="http://www.regents.umich.edu/">The Regents of the University of Michigan</a><br />This server maintained by the <a href="http://www.eecs.umich.edu/dco">EECS Departmental Computing Organization</a>.</div>

</body>
</html>