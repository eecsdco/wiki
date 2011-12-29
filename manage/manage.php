<?php

$global_path = "/y/wiki/global";
require_once("$global_path/management/wiki_management.class.php");
$manager = new EECS_WIKI_MANAGEMENT();
$wikis = $manager->wiki_list_all();
// set the shortname or redirect if not set
if ( isset($_REQUEST["w"]) AND $manager->wiki_exists($_REQUEST["w"]) ) $shortname = $_REQUEST["w"];
else header("Location: https://wiki.eecs.umich.edu/manage");
// get the list of admins for this wiki
$admins_local = $manager->wiki_users_get($shortname,"admins");
global $admins_dco;
$user = force_login(array_merge($admins_local,$admins_dco));
// check for incoming messages
$message = "";
if ( isset($_REQUEST["m"]) ) $message = urldecode($_REQUEST["m"]);
// get version
$link = readlink("/y/wiki/instances/$shortname");

?>

<html>
<head>
<title>EECS Wiki Server</title>
<link rel="stylesheet" href="manage.css" type="text/css" />
</head>
<body>

<p><a href="index.php">back</a> | welcome <?php echo $user; ?> | <a href="/global/cosign/logout.php">logout</a></p>

<?php if ( $message != "" ) echo "<div id='menu'><strong> $message </strong></div>"; ?>

<h1>EECS Wiki Server</h1>

<h2>Management Options for <?php echo $manager->wiki_option_get($shortname,"fullname"); ?></h2>

<?php
// show another menu only to DCO staff
global $admins_dco;
if ( in_array($user,$admins_dco) ) {
?>
<div id="menu"><b>DCO Staff Management Options</b><br />
	<small>current run path: <?php echo $link; ?></small><br />
	<a href="<?php echo "actions.php?w=$shortname&a=update_start"; ?>" onclick="return confirm('This process may take up to 30 minutes to complete. During the update process, there will be no screen output. Are you sure you want to continue?')">update to current release</a> | 
	<a href="<?php echo "actions.php?w=$shortname&a=update_start_beta"; ?>" onclick="return confirm('This process may take up to 30 minutes to complete. During the update process, there will be no screen output. Are you sure you want to continue? Beta releases are for testing only!')">update to beta release</a> | 
	<a href="actions.php?w=<?php echo $shortname; ?>&a=backup_start">backup this wiki</a> | 
	<a href="actions.php?w=<?php echo $shortname; ?>&a=wiki_delete">delete this wiki</a>
</div>
<?php } ?>

<h3>Name</h3>

<table>
<tr>
	<td width="160px"></td>
	<th>Value</th>
	<th width="300px">Description</th>
</tr>
<tr>
	<th>Short Name</th>
	<td><?php echo $shortname; ?></td>
	<td>determines the URL of this wiki<br />cannot be changed</td>
</tr>
<tr>
	<th rowspan="2">Full Name</th>
	<td><?php echo $manager->wiki_option_get($shortname,"fullname"); ?></td>
	<td>publicly viewable title</td>
</tr>
	<form action="actions.php" method="get">
	<td><input type="text" name="v" /><input type="submit" value="Modify" /></td>
	<td></td>
	<input type="hidden" name="w" value="<?php echo $shortname; ?>" />
	<input type="hidden" name="a" value="option_set" />
	<input type="hidden" name="o" value="fullname" />
	</form>
</table>


<h3>Security</h3>

<p>These settings define who can read and write to the wiki. Click the link to toggle the value. With the exception of 'blacklist', these settings override the group permissions below.</p>

<table>
<tr>
	<td width="160px"></td>
	<th>Read</th>
	<th>Edit</th>
	<th width="300px">Description</th>
</tr>
<tr>
	<th>Anonymous Users</th>
	<td><?php $url = "actions.php?a=option_set&w=$shortname&o=access_anonymous_read"; if ( $manager->wiki_option_get($shortname,"access_anonymous_read") ) echo "<a href='$url&v=0'>YES</a>"; else echo "<a href='$url&v=1'>NO</a>"; ?></td>
	<td><?php $url = "actions.php?a=option_set&w=$shortname&o=access_anonymous_edit"; if ( $manager->wiki_option_get($shortname,"access_anonymous_edit") ) echo "<a href='$url&v=0'>YES</a>"; else echo "<a href='$url&v=1'>NO</a>"; ?></td>
	<td>all users that are not logged in</td>
</tr>
<tr>
	<th>Authenticated UM Users</th>
	<td><?php $url = "actions.php?a=option_set&w=$shortname&o=access_um_read"; if ( $manager->wiki_option_get($shortname,"access_um_read") ) echo "<a href='$url&v=0'>YES</a>"; else echo "<a href='$url&v=1'>NO</a>"; ?></td>
	<td><?php $url = "actions.php?a=option_set&w=$shortname&o=access_um_edit"; if ( $manager->wiki_option_get($shortname,"access_um_edit") ) echo "<a href='$url&v=0'>YES</a>"; else echo "<a href='$url&v=1'>NO</a>"; ?></td>
	<td>all users with a valid UM uniquename or friend account</td>
</tr>
</table>


<h3>Group Permissions</h3>

<p>The following sections allow you to grant users access to this wiki. Access permissions are controlled by which group you add them to.</p>
<ul>
		<li>To add a UM person, enter their uniquename in the blank and click the "Add" button.</li>
		<li>To add a non-UM person, enter their email address in the blank and then click the "Add" button. Please note that non-UM people
will need to create a UM Friend Account in order to login after you have added them here. More information on UM Friend Accounts can be found on the <a href="http://www.itd.umich.edu/help/faq/friend.php">ITS website</a>.</li>
</ul>

<h4>Administrators</h4>
<p>Administrators can access this page, modify settings, change group memberships, read the wiki, and edit the wiki.</p>
<table>
	<tr>
		<th>User</th>
		<th>Actions</th>
	</tr>
	<?php
		$users = $manager->wiki_users_get($shortname,"admins");
		$remove_url = "actions.php?a=user_remove&w=$shortname&g=admins";
		foreach ( $users as $user ) echo "<tr><td>$user</td><td><a href='$remove_url&u=$user'>remove</a></td></tr>";
	?>
	<tr>
		<form action="actions.php" method="get">
		<td><input type="text" name="u" /></td>
		<td><input type="submit" value="Add" /></td>
		<input type="hidden" name="w" value="<?php echo $shortname; ?>" />
		<input type="hidden" name="a" value="user_add" />
		<input type="hidden" name="g" value="admins" />
		</form>
	</tr>
</table>

<h4>Members</h4>
<p>Members can read and edit the wiki.</p>
<table>
	<tr>
		<th>User</th>
		<th>Actions</th>
	</tr>
	<?php
		$users = $manager->wiki_users_get($shortname,"members");
		$remove_url = "actions.php?a=user_remove&w=$shortname&g=members";
		foreach ( $users as $user ) echo "<tr><td>$user</td><td><a href='$remove_url&u=$user'>remove</a></td></tr>";
	?>
	<tr>
		<form action="actions.php?" method="get">
		<td><input type="text" name="u" /></td>
		<td><input type="submit" value="Add" /></td>
		<input type="hidden" name="w" value="<?php echo $shortname; ?>" />
		<input type="hidden" name="a" value="user_add" />
		<input type="hidden" name="g" value="members" />
		</form>
	</tr>
</table>

<h4>Readers</h4>
<p>Readers can only read the wiki.</p>
<table>
	<tr>
		<th>User</th>
		<th>Actions</th>
	</tr>
	<?php
		$users = $manager->wiki_users_get($shortname,"readers");
		$remove_url = "actions.php?a=user_remove&w=$shortname&g=readers";
		foreach ( $users as $user ) echo "<tr><td>$user</td><td><a href='$remove_url&u=$user'>remove</a></td></tr>";
	?>
	<tr>
		<form action="actions.php" method="get">
		<td><input type="text" name="u" /></td>
		<td><input type="submit" value="Add" /></td>
		<input type="hidden" name="w" value="<?php echo $shortname; ?>" />
		<input type="hidden" name="a" value="user_add" />
		<input type="hidden" name="g" value="readers" />
		</form>
	</tr>
</table>

<h4>Blacklist</h4>
<p>Blacklisted users have no access to the wiki.<br />Useful for spam control when editing is allowed for all UM users.</p>
<table>
	<tr>
		<th>User</th>
		<th>Actions</th>
	</tr>
	<?php
		$users = $manager->wiki_users_get($shortname,"blacklist");
		$remove_url = "actions.php?a=user_remove&w=$shortname&g=blacklist";
		foreach ( $users as $user ) echo "<tr><td>$user</td><td><a href='$remove_url&u=$user'>remove</a></td></tr>";
	?>
	<tr>
		<form action="actions.php" method="get">
		<td><input type="text" name="u" /></td>
		<td><input type="submit" value="Add" /></td>
		<input type="hidden" name="w" value="<?php echo $shortname; ?>" />
		<input type="hidden" name="a" value="user_add" />
		<input type="hidden" name="g" value="blacklist" />
		</form>
	</tr>
</table>


<br />
<br />

<div id="footer">&copy; <?php date_default_timezone_set("America/Detroit"); echo date("Y"); ?> <a href="http://www.regents.umich.edu/">The Regents of the University of Michigan</a><br />This server maintained by the <a href="http://www.eecs.umich.edu/dco">EECS Departmental Computing Organization</a>.</div>


</body>
</html>