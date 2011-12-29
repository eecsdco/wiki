<?php

// WIKI MANAGEMENT CLASS FOR EECS WIKI SERVER
// Author: Matt Colf mcolf@umich.edu
// Disclaimer: It's not perfect, but neither are you. 

// load global configuration
$global_path = "/y/wiki/global";
require_once("$global_path/config.php");

class EECS_WIKI_MANAGEMENT
{
	private $db;
	private $location_source;	
	private $location_data;			
	private $location_default;		

	public function __construct()
	{
		global $global_path, $global_db_type, $global_db_server, $global_db_user, $global_db_password;
		$this->global_db_type = $global_db_type;
		$this->global_db_server = $global_db_server;
		$this->global_db_user = $global_db_user;
		$this->global_db_password = $global_db_password;
		$this->location_source = "$global_path/mediawiki";		# location of MediaWiki source files base
		$this->location_data = "$global_path/data";				# location of instance writable data area base
		$this->location_default = "$global_path/default";		# location of default files and configurations
		$this->database_connect();
	}
	
	// connect to the database and setup tables, if necessary
	private function database_connect()
	{
		$this->db = new mysqli($this->global_db_server,$this->global_db_user,$this->global_db_password,"eecs_wiki_manager");
		if ( $this->db->connect_error ) die("Unable to connect to management database! ".$this->db->connect_error);
		$sql = "CREATE TABLE IF NOT EXISTS wikis (
					shortname VARCHAR(50) UNIQUE NOT NULL,
					fullname VARCHAR(50) UNIQUE NOT NULL,
					access_anonymous_read BOOLEAN DEFAULT FALSE,
					access_anonymous_edit BOOLEAN DEFAULT FALSE,
					access_um_read BOOLEAN DEFAULT FALSE,
					access_um_edit BOOLEAN DEFAULT FALSE,
					db_name VARCHAR(50) UNIQUE NOT NULL
				) ENGINE = INNODB";
		if ( !$this->db->query($sql) ) die("Unable to create/update table wikis! ".$this->db->error);
		$sql = "CREATE TABLE IF NOT EXISTS users (
					shortname VARCHAR(50) NOT NULL,
					username VARCHAR(100) NOT NULL,
					groupname VARCHAR(20) NOT NULL,
					FOREIGN KEY (shortname) REFERENCES wikis (shortname) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE = INNODB";
		if ( !$this->db->query($sql) ) die("Unable to create/update table users! ".$this->db->error);
	}
	
	// check if a wiki exists
	public function wiki_exists($shortname)
	{
		if ( !is_string($shortname) ) return FALSE;
		if ( $shortname == "" ) return FALSE;
		$statement = $this->db->prepare("SELECT shortname FROM wikis WHERE shortname=?");
		$statement->bind_param("s",$shortname);
		$statement->execute();
		$statement->bind_result($name);
		$statement->fetch();
		if ( $name == $shortname ) return TRUE;
		$statement->close();
		return FALSE;
	}
	
	// get a list of all wikis
	public function wiki_list_all()
	{
		$all = array();
		$statement = $this->db->prepare("SELECT shortname, fullname, access_anonymous_read, access_um_read FROM wikis ORDER BY shortname ASC");
		$statement->execute();
		$statement->bind_result($shortname,$fullname,$access_anonymous_read,$access_um_read);
		while ( $statement->fetch() ) {
			$wiki = array("shortname" => $shortname, "fullname" => $fullname, "access_anonymous_read" => $access_anonymous_read, "access_um_read" => $access_um_read);
			$all[$wiki["shortname"]] = $wiki;
		}
		return $all;
	}

	// set a wiki option
	public function wiki_option_set($shortname,$option,$value)
	{
		// admin check
		if ( !$this->wiki_is_admin($shortname,$_SERVER["REMOTE_USER"]) ) return FALSE;
		// check wiki name
		if ( !$this->wiki_exists($shortname) ) return FALSE;
		// update the database
		if ( $option == "fullname" ) $sql = "UPDATE wikis SET fullname = ? WHERE shortname = ?";
		else if ( $option == "access_anonymous_read" ) $sql = "UPDATE wikis SET access_anonymous_read = ? WHERE shortname = ?";
		else if ( $option == "access_anonymous_edit" ) $sql = "UPDATE wikis SET access_anonymous_edit = ? WHERE shortname = ?";
		else if ( $option == "access_um_read" ) $sql = "UPDATE wikis SET access_um_read = ? WHERE shortname = ?";
		else if ( $option == "access_um_edit" ) $sql = "UPDATE wikis SET access_um_edit = ? WHERE shortname = ?";
		else if ( $option == "db_name" ) $sql = "UPDATE wikis SET db_name = ? WHERE shortname = ?";
		else return FALSE;
		// update database
		$statement = $this->db->prepare($sql) or die("Unable to compile for option $option on wiki $shortname:".$this->db->error);
		$statement->bind_param("ss",$value,$shortname);
		$result = $statement->execute();
		$statement->close();
		return $result;
	}
	
	// get the value of a wiki option
	public function wiki_option_get($shortname,$option)
	{
		$value = "";
		// check wiki name
		if ( !$this->wiki_exists($shortname) ) return FALSE;
		// load static options
		if ( $option == "local_data_path" ) return $this->location_data."/$shortname";
		if ( $option == "db_type" ) return $this->global_db_type;
		if ( $option == "db_server" ) return $this->global_db_server;
		if ( $option == "db_user" ) return $this->global_db_user;
		if ( $option == "db_password" ) return $this->global_db_password;
		if ( $option == "upload_directory" ) return "/global/data/$shortname/images";
		if ( $option == "upload_path" ) return $this->location_data."/$shortname/images";
		if ( $option == "default_skin" ) return "vector";
		// load dynamic options
		if ( $option == "fullname" ) $sql = "SELECT fullname FROM wikis WHERE shortname = ?";
		else if ( $option == "access_anonymous_read" ) $sql = "SELECT access_anonymous_read FROM wikis WHERE shortname = ?";
		else if ( $option == "access_anonymous_edit" ) $sql = "SELECT access_anonymous_edit FROM wikis WHERE shortname = ?";
		else if ( $option == "access_um_read" ) $sql = "SELECT access_um_read FROM wikis WHERE shortname = ?";
		else if ( $option == "access_um_edit" ) $sql = "SELECT access_um_edit FROM wikis WHERE shortname = ?";
		else if ( $option == "db_name" ) $sql = "SELECT db_name FROM wikis WHERE shortname = ?";
		else return FALSE;
		// pull from database
		$statement = $this->db->prepare($sql) or die("Unable to compile for option $option on wiki $shortname:".$this->db->error);
		$statement->bind_param("s",$shortname);
		$statement->execute() or die("Unable to execute for option $option on wiki $shortname!");
		$statement->bind_result($value);
		$statement->fetch();
		$statement->close();
		return $value;
	}
	
	// add a user to a wiki group (admins, members, readers, blacklist)
	public function wiki_users_add($shortname,$group,$user)
	{
		// admin check
		if ( !$this->wiki_is_admin($shortname,$_SERVER["REMOTE_USER"]) ) return FALSE;
		// check wiki name
		if ( !$this->wiki_exists($shortname) ) return FALSE;
		// check group name
		if ( ($group == "admins") OR ($group == "members") OR ($group == "readers") OR ($group == "blacklist") ) {
			// don't allow external admins
			if ( (strstr($user,"@") != FALSE) AND ($group == "admins") ) return FALSE;
			// don't allow special characters
			if ( strstr($user,"+") != FALSE ) return FALSE;
			// add the user
			$statement = $this->db->prepare("INSERT IGNORE INTO users (shortname,username,groupname) VALUES (?,?,?)");
			$statement->bind_param("sss",$shortname,$user,$group);
			$result = $statement->execute();
			$statement->close();
			if ( $result ) {
				// let the user know they have been added
				if ( strstr($user,"@") == FALSE ) {
					$to = "$user@umich.edu";
					$message = "Your username, $user, has been granted access to the $shortname wiki on the EECS Wiki Server. You have been placed in the $group access group.\r\n";
					$message .=	"Please login at https://wiki.eecs.umich.edu/$shortname to verify that your account is working correctly.";
				}
				else {
					$to = $user;
					$message = "Your username, $user, has been granted access to the $shortname wiki on the EECS Wiki Server. You have been placed in the $group access group.\r\n\r\n";
					$message .= "If you do not already have one, you will need to create a UM Friend Account in order to login. You can this this by going to https://friend.weblogin.umich.edu/friend/. Please note that you will need to use the email address $user in order for authentication to work properly.\r\n\r\n";
					$message .=	"Once you account has been created, please login at https://wiki.eecs.umich.edu/$shortname to verify that your account is working correctly.";
				}
				$subject = "You have been granted access to the $shortname wiki";
				$this->email($to,$subject,$message);
				
				// added to the admins group? let the other admins know
				if ( $group == "admins" ) {
					$admins = $this->wiki_users_get($shortname,"admins");
					foreach ( $admins as $admin ) {
						$to = "$admin@umich.edu";
						$subject = "$shortname wiki security notice - new admin added";
						$message = "This is a security notice.\r\n\r\n";
						$message .= "Please be aware that ".$_SERVER["REMOTE_USER"]." has just granted the user $user admin access to the $shortname wiki.\r\n\r\n";
						$this->email($to,$subject,$message);
					}
				}
			}			
			return $result;
		}
		return FALSE;
	}
	
	// remove a user from a wiki group
	public function wiki_users_remove($shortname,$group,$user)
	{
		// admin check
		if ( !$this->wiki_is_admin($shortname,$_SERVER["REMOTE_USER"]) ) return FALSE;
		// check wiki name
		if ( !$this->wiki_exists($shortname) ) return FALSE;
		// check group name
		if ( ($group == "admins") OR ($group == "members") OR ($group == "readers") OR ($group == "blacklist") ) {
			$statement = $this->db->prepare("DELETE FROM users WHERE shortname = ? AND username = ? AND groupname = ?");
			$statement->bind_param("sss",$shortname,$user,$group);
			$result = $statement->execute();
			$statement->close();
			return $result;
		}
		return FALSE;
	}
	
	// get all users with access to this wiki, optional: from a selected group
	public function wiki_users_get($shortname,$group = NULL)
	{
		$users = array();
		$statement = $this->db->prepare("SELECT username FROM users WHERE shortname = ? AND groupname = ? ORDER BY username ASC");
		$statement->bind_param("ss",$shortname,$groupquery);
		if ( $group == "admins" OR $group == NULL ) {
			$groupquery = "admins";
			$admins = array();
			$statement->execute();
			$statement->bind_result($username);
			while ( $statement->fetch() ) $admins[] = $username;
			if ( $group == "admins" ) {
				$statement->close();
				return $admins;
			}
			else $users["admins"] = $admins;
		}
		if ( $group == "members" OR $group == NULL ) {
			$groupquery = "members";
			$members = array();
			$statement->execute();
			$statement->bind_result($username);
			while ( $statement->fetch() ) $members[] = $username;
			if ( $group == "members" ) {
				$statement->close();
				return $members;
			}
			else $users["members"] = $members;
		}
		if ( $group == "readers" OR $group == NULL ) {
			$groupquery = "readers";
			$readers = array();
			$statement->execute();
			$statement->bind_result($username);
			while ( $statement->fetch() ) $readers[] = $username;
			if ( $group == "readers" ) {
				$statement->close();
				return $readers;
			}
			else $users["readers"] = $readers;
		}
		if ( $group == "blacklist" OR $group == NULL ) {
			$groupquery = "blacklist";
			$blacklist = array();
			$statement->execute();
			$statement->bind_result($username);
			while ( $statement->fetch() ) $blacklist[] = $username;
			if ( $group == "blacklist" ) {
				$statement->close();
				return $blacklist;
			}
			else $users["blacklist"] = $blacklist;
		}
		$statement->close();
		return $users;
	}
	
	// fully backup a wiki (database and data directory)
	public function wiki_backup($shortname,$reason = "backup")
	{
		// set the date stamp
		date_default_timezone_set("America/Detroit");
		$datestamp = date("d-M-Y_G:i:s");
		
		// backup the data directory
		global $global_path;
		$data_path = "$global_path/data/$shortname";
		$backup_path = "$global_path/backup/$shortname.data.$reason.$datestamp";
		shell_exec("cp -a $data_path $backup_path");
		
		// backup the database
		$username = $this->wiki_option_get($shortname,"db_user");
		$password = $this->wiki_option_get($shortname,"db_password");
		$database = $this->wiki_option_get($shortname,"db_name");
		$backup_location = "$global_path/backup/$shortname.$reason.$datestamp.sql";
		shell_exec("mysqldump -u $username --password=$password --opt --databases $database > $backup_location");
		
		$details = array(
			"data" => $backup_path,
			"sql" => $backup_location
		);
		return $details;
	}
	
	// create a new wiki from scratch
	public function wiki_create($shortname)
	{
		// check shortname for validity
		$shortname = strtolower(trim(rtrim($shortname)));
		if ( preg_replace("/[a-zA-Z0-9]/","",$shortname) != "" ) return FALSE;
		// check for existing wiki
		if ( $this->wiki_exists($shortname) ) return FALSE;
		// add a new wiki entry
		$fullname = "CHANGE ME!";
		$db_name = "$shortname-wiki";
		$statement = $this->db->prepare("INSERT INTO wikis (shortname,fullname,db_name) VALUES (?,?,?)");
		$statement->bind_param("sss",$shortname,$fullname,$db_name);
		$result = $statement->execute();
		$statement->close();
		
		if ( $result ) {
		
			// setup the instance data path
			global $global_path;
			shell_exec("cp -a $global_path/data/default $global_path/data/$shortname");
			
			// create the database
			$db_user = $this->wiki_option_get($shortname,"db_user");
			$db_password = $this->wiki_option_get($shortname,"db_password");
			$db_name = $this->wiki_option_get($shortname,"db_name");
			shell_exec("mysqladmin -u $db_user --password=$db_password create $db_name");
			
			// load the default schema
			$default_schema = "$global_path/mediawiki/current/maintenance/tables.sql";
			shell_exec("mysql -u $db_user --password=$db_password $db_name < $default_schema");
			
			// setup the instance link
			$instance_path = "/y/wiki/instances/$shortname";
			$current_path = "../global/mediawiki/current";
			shell_exec("ln -s $current_path $instance_path");
			
			return TRUE;
		}
		return FALSE;	
	}
	
	// delete a wiki completely (scary!)
	public function wiki_delete($shortname)
	{
		// admin check
		if ( !$this->wiki_is_admin($shortname,$_SERVER["REMOTE_USER"]) ) return FALSE;
				
		// backup the entire wiki
		$this->wiki_backup($shortname,"deleted");
		sleep(1);
		
		// remove the instance path
		$instance_path = "/y/wiki/instances/$shortname";
		shell_exec("rm -f $instance_path");
		
		// remove the data path
		global $global_path;
		$data_path = "$global_path/data/$shortname";
		shell_exec("rm -rf $data_path");
		
		// drop the instance database
		$db_user = $this->wiki_option_get($shortname,"db_user");
		$db_password = $this->wiki_option_get($shortname,"db_password");
		$db_name = $this->wiki_option_get($shortname,"db_name");
		shell_exec("mysqladmin -u $db_user --password=$db_password drop $db_name");
		
		// delete the wiki entry
		$statement = $this->db->prepare("DELETE FROM wikis WHERE shortname = ?");
		$statement->bind_param("s",$shortname);
		$result = $statement->execute();
		$statement->close();
		
		return $result;
	}
	
	// start the wiki update process (creates the temporary upgrade path)
	private function wiki_update_start($shortname,$beta = false)
	{
		// admin check
		if ( !$this->wiki_is_admin($shortname,$_SERVER["REMOTE_USER"]) ) return FALSE;
	
		global $global_path;
		
		// backup the database
		$this->wiki_backup($shortname,"update");
	
		// copy the current mediawiki release into the upgrade path
		$current_path = "$global_path/mediawiki/current";
		$beta_path = "$global_path/mediawiki/beta";
		$upgrade_path = "$global_path/mediawiki/upgrade";
		shell_exec("rm -rf $upgrade_path");
		shell_exec("mkdir $upgrade_path"); 
		if ( $beta ) shell_exec("cp -r $beta_path/* $upgrade_path ");
		else shell_exec("cp -r $current_path/* $upgrade_path ");
		sleep(1);
		
		// create a dummy localsettings.php file
		shell_exec("mv $upgrade_path/LocalSettings.php $upgrade_path/LocalSettings.old.php");
		$ols = file("$upgrade_path/LocalSettings.old.php");
		$pointer = fopen("$upgrade_path/LocalSettings.php","w");
		$upgrade_key = mt_rand(1111111111111111,9999999999999999);
		foreach ( $ols as $line ) {
			
			// statically set variables as needed
			// needed to allow non-PHP parsing of the config file during upgrade
			if ( strpos($line,'shortname') == 1 ) $line = '$shortname = '."'$shortname';\n";
			if ( strpos($line,'wgDBtype') == 1 ) $line = '$wgDBtype = '."'".$this->wiki_option_get($shortname,"db_type")."';\n";
			if ( strpos($line,'wgDBserver') == 1 ) $line = '$wgDBserver = '."'".$this->wiki_option_get($shortname,"db_server")."';\n";
			if ( strpos($line,'wgDBname') == 1 ) $line = '$wgDBname = '."'".$this->wiki_option_get($shortname,"db_name")."';\n";
			if ( strpos($line,'wgDBuser') == 1 ) $line = '$wgDBuser = '."'".$this->wiki_option_get($shortname,"db_user")."';\n";
			if ( strpos($line,'wgDBpassword') == 1 ) $line = '$wgDBpassword = '."'".$this->wiki_option_get($shortname,"db_password")."';\n";
			if ( strpos($line,'wgUpgradeKey') == 1 ) $line = '$wgUpgradeKey = '."'".$upgrade_key."';\n";
			if ( strpos($line,'upgrade_running') == 1 ) $line = '$upgrade_running = '."TRUE;\n";
			if ( strpos($line,'wgUploadDirectory') == 1 ) $line = '$wgUploadDirectory = '."'".$this->wiki_option_get($shortname,"upload_directory")."';\n";
			if ( strpos($line,'wgUploadPath') == 1 ) $line = '$wgUploadPath = '."'".$this->wiki_option_get($shortname,"upload_path")."';\n";
			if ( strpos($line,'admins_local') == 1 ) $line = "";;
			if ( strpos($line,'members_local') == 1 ) $line = "";;
			if ( strpos($line,'readers_local') == 1 ) $line = "";;
			if ( strpos($line,'blacklist_local') == 1 ) $line = "";;
			if ( strpos($line,'access_anonymous_read') == 1 ) $line = "";;
			if ( strpos($line,'access_anonymous_edit') == 1 ) $line = "";;
			if ( strpos($line,'access_um_read') == 1 ) $line = "";;
			if ( strpos($line,'access_um_edit') == 1 ) $line = "";;
			if ( strpos($line,'wgSitename') == 1 ) $line = '$wgSitename = '."'UPGRADE IN PROGRESS';\n";
			if ( strpos($line,'local_data_path') == 1 ) $line = '$local_data_path = '."'".$this->wiki_option_get($shortname,"local_data_path")."';\n";
			if ( strpos($line,'wgDefaultSkin') == 1 ) $line = '$wgDefaultSkin = '."'".$this->wiki_option_get($shortname,"default_skin")."';\n";
			fwrite($pointer,$line);
		}
		fclose($pointer);
		shell_exec("chmod -R 775 $upgrade_path");
		$data_path = $this->wiki_option_get($shortname,"local_data_path");
		shell_exec("chmod -R 775 $data_path/images");
		
		// point the instance link to notice path (prevent use of wiki during update)
		$notice_path = "/y/wiki/global/down-notice";
		$instance_path = "/y/wiki/instances/$shortname";
		shell_exec("rm -f $instance_path");
		shell_exec("ln -s $$notice_path $instance_path");
		sleep(1);
		
		// give the user an upgrade key so that they can run the update script manually
		return $upgrade_key;
	}
	
	// end the wiki update process (remove the upgrade path)
	private function wiki_update_end($shortname, $beta = false)
	{
		// admin check
		if ( !$this->wiki_is_admin($shortname,$_SERVER["REMOTE_USER"]) ) return FALSE;
		
		// point instance link to current (or beta) mediawiki release
		$instance_path = "/y/wiki/instances/$shortname";
		$current_path = "../global/mediawiki/current";
		$beta_path = "../global/mediawiki/beta";
		shell_exec("rm -f $instance_path");
		if ( $beta ) shell_exec("ln -s $beta_path $instance_path");
		else shell_exec("ln -s $current_path $instance_path");
		
		// remove the upgrade path
		shell_exec("rm -rf $upgrade_path");
		
	}
	
	// run the update process (takes a while! yes, this could be improved)
	public function wiki_update_run($shortname, $beta = false)
	{
		// admin check
		if ( !$this->wiki_is_admin($shortname,$_SERVER["REMOTE_USER"]) ) return FALSE;
		
		// increase execution time
		ini_set("max_execution_time","300");
		
		// backup the database and prepare the update directory
		$key = $this->wiki_update_start($shortname,$beta);
		
		// run the update script
		global $global_path;
		$upgrade_path = "$global_path/mediawiki/upgrade";
		shell_exec("/usr/bin/php $upgrade_path/maintenance/update.php");
		
		// update user preferences
		shell_exec("/usr/bin/php $upgrade_path/maintenance/userOptions.php skin --nowarn --old 'monobook' --new 'vector'");
		shell_exec("/usr/bin/php $upgrade_path/maintenance/userOptions.php skin --nowarn --old 'cseg' --new 'vector'");

		// clean up after the scipt has finished
		$this->wiki_update_end($shortname,$beta);
		
		return TRUE;
	}
	
	// check if a user is an admin for a wiki
	public function wiki_is_admin($shortname,$username)
	{
		global $admins_dco;
		// check dco admins list
		if ( in_array($username,$admins_dco) ) return TRUE;
		// check local admins list
		if ( in_array($username,$this->wiki_users_get($shortname,"admins")) ) return TRUE;
		return FALSE;
	}
	
	// gets the wiki shortname from the calling URI 
	public function wiki_shortname_get()
	{
		$URI = explode("/",$_SERVER["REQUEST_URI"]);
		if ( count($URI) > 1 ) {
			if ( strlen($URI[1]) < 2 ) die("Shortname too... short.");
			// check if a trailing slash messed up the rewrite
			if ( $URI[1] == "instances" ) {
				echo "<h1>Invalid URI</h1>";
				echo "<p>Please do not include a trailing slash when opening a wiki on this server, it makes the resource loader angry.</p>";
				if ( count($URI) > 2 ) echo "<p>You probably meant to go <a href='/".$URI[2]."'>here</a>. Please update your bookmarks.</p>";
				echo "<p>If you continue to see this error, please contact <a href='http://www.eecs.umich.edu/dco/contact.php'>DCO Support</a>.</p>";
				exit(1);
			}
			else return $URI[1];
		}
		else die("Bad URI!");
	}
	
	// send an email
	private function email($to,$subject,$message,$cc = NULL)
	{
		$headers = "From: EECS Wiki Server <help@eecs.umich.edu>\r\n";
		if ( $cc != NULL ) $headers .= "CC: $cc\r\n";
		$subject = "[EECS Wiki Server] ".$subject;
		$contents = "This is an automated message, please do not reply.\r\n\r\n";
		$contents .= "Greetings,\r\n\r\n";
		$contents .= $message . "\r\n\r\n";
		$contents .= "If you have any questions or experience trouble with your account, please contact EECS IT Support by emailing help@eecs.umich.edu.\r\n\r\n";
		$contents .= "---\r\nEECS IT Support\r\nhelp@eecs.umich.edu";
		return mail($to,$subject,$contents,$headers);
	}
	
}

// force a user to login using cosign
function force_login($group = NULL)
{
	//echo "Cosign_Service: ".$_SERVER["COSIGN_SERVICE"]."<br />";
	//echo "Remote_User: ".$_SERVER["REMOTE_USER"]."<br />";
	//echo "Cosign_Factor: ".$_SERVER["COSIGN_FACTOR"]."<br />";
	//echo "Remote_Realm ".$_SERVER["REMOTE_REALM"]."<br />";
	
	if (isset($_SERVER['REMOTE_USER']) AND ( stristr($_SERVER["COSIGN_FACTOR"],"UMICH.EDU") OR stristr($_SERVER["COSIGN_FACTOR"],"friend" ) ) ) {
		if ( is_array($group) ) {
			if (in_array($_SERVER['REMOTE_USER'],$group)) return $_SERVER['REMOTE_USER'];
			else die("Permission Denied.");
		}
		return $_SERVER['REMOTE_USER'];
	}
	else {
		// redirect to login page
		if ( $redirect == "" ) $redirect = "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		header("Location: $cosign_login_script?redirect=".urlencode($redirect));
		exit();
	}
}

?>