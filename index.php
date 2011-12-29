<?php

$global_path = "/y/wiki/global";
require_once("$global_path/management/wiki_management.class.php");
$manager = new EECS_WIKI_MANAGEMENT();
$wikis = $manager->wiki_list_all();

?>

<html>
<head>
<link rel="stylesheet" href="manage.css" type="text/css" />
<title>EECS Wiki Server</title>
</head>
<body>

<h1>EECS Wiki Server</h1>

<div id="menu"><a href="/manage">wiki management</a></div>

<table>
<?php
	$count = 0;
	foreach ( $wikis as $wiki ) {
		$security = "";
		if ( $wiki["access_anonymous_read"] ) $security = "Public";
		else if ( $wiki["access_um_read"] ) $security = "UM Only";
		else $security = "Private";
		if ( ($count == 0) OR (($count%3 ) == 0) ) echo "<tr>";
		echo "<td><a href='/".$wiki["shortname"]."'>".$wiki["fullname"]."</a><br /><small>$security</small></td>";
		if ( ($count+1)%3 == 0 ) echo "</tr>";
		$count = $count + 1;
	}
?>
</table>

<div id="footer">&copy; <?php date_default_timezone_set("America/Detroit"); echo date("Y"); ?> <a href="http://www.regents.umich.edu/">The Regents of the University of Michigan</a><br />This server maintained by the <a href="http://www.eecs.umich.edu/dco">EECS Departmental Computing Organization</a>.</div>

</body>
</html>