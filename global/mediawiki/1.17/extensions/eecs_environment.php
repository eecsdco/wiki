<?php

# for Special::Version:
$wgExtensionCredits['other'][] = array(
    'name' => 'EECS Wiki Management Extension',
    'author' => 'Matt Colf',
    'url' => 'http://www.eecs.umich.edu/dco',
    'version' => 'v1.2',
    'description'=>'Modifies MediaWiki for the EECS environment. Adds support for UM Cosign authentication, automated group memberships, and programatic access restrictions.',
);


###################################################################
# CONFIGURATION
###################################################################

## dependencies from LocalSettings.php
// global $shortname
//// array('user1','user2')
// global $admins_dco, $admins_local, $members_dco, $members_local
// global $readers_dco, $readers_local, $blacklist_dco, $blacklist_local
//// TRUE/FALSE
// global $access_anonymous_read, $access_anonymous_edit;
// global $access_um_read, $access_um_edit;

## mediawiki hook dependencies
// $wgHooks['UserLoadFromSession']
// $wgHooks['PersonalUrls']
// $wgHooks['UserLogoutComplete']
// $wgHooks['SpecialPage_initList']
// $wgHooks['UserLoginForm']
// $wgHooks['UserLoadAfterLoadFromSession']
// $wgHooks['SkinBuildSidebar']
// $wgHooks['ParserBeforeTidy']

// debug control
global $debug;
if ( !isset($debug) ) $debug = FALSE;

// cache control
global $disable_cache;
if ( !isset($disable_cache) ) $disable_cache = FALSE;

// cosign script urls
$server_name = $_SERVER["SERVER_NAME"];
$cosign_login_script = "https://$server_name/global/cosign/login.php";
$cosign_logout_script = "https://$server_name/global/cosign/logout.php";

// wiki management path
$path_manage = "/manage/manage.php";


###################################################################
# COSIGN AUTHENTICATION
# Reference:
# http://svn.wikimedia.org/doc/classAuthPlugin.html
###################################################################

// check for a valid cosign factor
function cosign_valid_user()
{
	if ( isset($_SERVER['REMOTE_USER']) ) {
		// UM user
		if ( stristr($_SERVER["COSIGN_FACTOR"],"UMICH.EDU") ) return TRUE;
		// external user
		if ( stristr($_SERVER["COSIGN_FACTOR"],"friend") ) return TRUE;
		// unknown user
		die("Unable to determine user affiliation for Cosign factor '".$_SERVER["COSIGN_FACTOR"]."'. Please contact your system administrator.");
	}
	else return FALSE;
}

// perform cosign authentication an
function cosign_auth($user,&$result)
{
	if ( cosign_valid_user() )
	{			
		$user = User::newFromName($_SERVER['REMOTE_USER']);
		$user->SetupSession();
		$user->setCookies();
		if ( $user->getID() == 0 )
		{
			// first time visitor
			$user->addToDatabase();
			$user->setToken();
			$email = strtolower($user->getName())."@umich.edu";
			$user->setEmail($email);
		}
	}
	return TRUE;
}

// force a cosign logoff event
function force_cosign_logout(&$user, &$inject_html, $old_name)
{
	global $cosign_logout_script, $shortname;
	$redirect = "https://".$_SERVER['SERVER_NAME']."/$shortname/index.php/Main_Page";
	// redirect the user to the cosign logout script
	header("Location: $cosign_logout_script?redirect=".urlencode($redirect));
	exit();
}

// force a cosign login event
function force_cosign_login($redirect = "")
{
	global $cosign_login_script;
	if ( $redirect == "" ) $redirect = "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	// redirect the user to the cosign login script
	header("Location: $cosign_login_script?redirect=".urlencode($redirect));
	exit();
}

// modify the top personal links
function setup_links(&$personal_urls,&$title)
{
	global $wgUser;
	if ( cosign_valid_user() )
	{
		$groups = $wgUser->getEffectiveGroups(TRUE);
		if ( in_array("sysop",$groups) ) 
		{
			global $path_manage, $shortname;
			$personal_urls['manage']['text'] = "Manage Wiki";
			$personal_urls['manage']['href'] = "$path_manage?w=$shortname";
		}
	}
	else if ( $wgUser->isLoggedIn() ) 
	{	
		// the login session has expired!
		force_cosign_login();
	}
	// hide normal login link
	//unset($personal_urls['login']);

	return TRUE;
}

// disable special pages
function disable_special_pages(&$list) {
		// not needed with current login style 
		// using force_cosign_login() and force_cosign_logout() instead
        return TRUE;
}

// modify the normal login process
function setup_login_form($template)
{
	$redirect = substr(strrchr($template->data["action"],"&returnto="),strlen("&returnto="));
	global $wgScriptPath;
	$redirect = "https://".$_SERVER['SERVER_NAME']."$wgScriptPath/index.php?$redirect";
	// interrupt normal login process and redirect to cosign login instead
	force_cosign_login($redirect);
}

// extend the authentication interface to support cosign
class cosign extends AuthPlugin {

	function __construct()
	{
		// register global hooks
		global $wgHooks;
        $wgHooks['UserLoadFromSession'][] = 'cosign_auth';
        $wgHooks['PersonalUrls'][] = 'setup_links';
		$wgHooks['UserLogoutComplete'][] = 'force_cosign_logout';
		$wgHooks['SpecialPage_initList'][]= 'disable_special_pages';
		$wgHooks['UserLoginForm'][] = 'setup_login_form';
	}
	
	function addUser($user,$password,$email = '',$realname = '')
	{
		return TRUE;
	}
	
	function allowPasswordChange()
	{
		return FALSE;
	}
	
	function authenticate($username,$password) 
	{
		if ( cosign_valid_user() ) return TRUE;
		else return FALSE;
	}
	
	function autoCreate()
	{
		return TRUE;
	}
	
	function canCreateAccounts() 
	{
		return FALSE;
	}

	function getCanonicalName($username)
	{
		return $username;
	}
	
	function initUser(&$user,$autocreate = FALSE)
	{
		// optionally set user preferences upon creation
	}
	
	function modifyUITemplate(&$template,&$type)
	{
		$template->set( 'usedomain', FALSE );
	}
	
	function setDomain($domain)
	{
		$this->domain = $domain;
	}
	
	function setPassword($username,$password)
	{
		return FALSE;
	}
	
	function strict()
	{
		return TRUE;
	}
	
	function updateUser(&$user)
	{
		// update information for non-anoymous accounts upon login
		if ( !$user->isAnon() ) 
		{
			// force email address to <username>@umich.edu
			$email = strtolower($user->getName())."@umich.edu";
			if ( $email != $user->getEmail() )
			{
				$user->setEmail($email);
			}
		
		}
		
		return TRUE;
	}
	
	function userExists($username)
	{
		if ( cosign_valid_user() ) return TRUE;
		else return FALSE;
	}
	
	function validDomain($domain)
	{
		return TRUE;
	}

}

// replace default authentication
global $wgAuth;
$wgAuth = new cosign();

###################################################################
# AUTOMATIC USER RIGHTS ELEVATION
###################################################################

class UserRightsElevation {
	
	private $admins = array();			# full admin access
	private $members = array();			# read and write access
	private $readers = array();			# read only access
	private $blacklist = array();		# no access
	private $current_user;
		
	public function __construct($user)
	{
		// set current user
		$this->current_user = $user;
		
		// check for user list definitions
		global $admins_dco, $admins_local, $members_dco, $members_local;
		global $readers_dco, $readers_local, $blacklist_dco, $blacklist_local;
		if ( !isset($admins_dco) OR !is_array($admins_dco) ) $admins_dco = array();
		if ( !isset($admins_local) OR !is_array($admins_local) ) $admins_local = array();
		if ( !isset($members_dco) OR !is_array($members_dco) ) $members_dco = array();
		if ( !isset($members_local) OR !is_array($members_local) ) $members_local = array();
		if ( !isset($readers_dco) OR !is_array($readers_dco) ) $readers_dco = array();
		if ( !isset($readers_local) OR !is_array($readers_local) ) $readers_local = array();
		if ( !isset($blacklist_dco) OR !is_array($blacklist_dco) ) $blacklist_dco = array();
		if ( !isset($blacklist_local) OR !is_array($blacklist_local) ) $blacklist_local = array();
		
		// build elevation lists
		$this->admins = array_merge($admins_dco,$admins_local);
		$this->members = array_merge($members_dco,$members_local);
		$this->readers = array_merge($readers_dco,$readers_local);
		$this->blacklist = array_diff(array_merge($blacklist_dco,$blacklist_local),$this->admins);
	}
	
	public function manage_rights()
	{
		$username = strtolower($this->current_user->mName);
		$current_user_groups = $this->current_user->getEffectiveGroups(TRUE);
		$this->print_debug("Current user groups for $username:");
		$this->print_debug(implode(" :: ",$current_user_groups));
		
		// blacklist check
		if ( in_array($username,$this->blacklist) )
		{
			die("Your account ($username) has been blacklisted.");
		}
		// add to admin group
		if ( in_array($username,$this->admins) ) 
		{
			if ( !in_array("sysop",$current_user_groups) ) {
				$this->current_user->addGroup("sysop");
				$this->print_debug("User added to sysop group.");
			}
			else $this->print_debug("User is already in sysop group. No action required.");
			if ( !in_array("bureaucrat",$current_user_groups) ) {
				$this->current_user->addGroup("bureaucrat");
				$this->print_debug("User added to bureaucrat group");
			}
			else $this->print_debug("User is already in bureaucrat group. No action required.");
		}
		// remove from admin group
		else
		{
			if ( in_array("sysop",$current_user_groups) ) {
				$this->current_user->removeGroup("sysop");
				$this->print_debug("User removed from sysop group.");
			}
			else $this->print_debug("User not in sysop group. No action required.");
			if ( in_array("bureaucrat",$current_user_groups) ) {
				$this->current_user->removeGroup("bureaucrat");
				$this->print_debug("User removed from bureaucrat group.");
			}
			else $this->print_debug("User not in bureaucrat group. No action required.");
		}
		// add to member group (read + write)
		if ( in_array($username,$this->members) )
		{
			if ( !in_array("member",$current_user_groups) ) {
				$this->current_user->addGroup("member");
				$this->print_debug("User added to member group.");
			}
			else $this->print_debug("User is already in member group. No action required.");
		}
		// remove from member group
		else
		{
			if ( in_array("member",$current_user_groups) ) {
				$this->current_user->removeGroup("member");
				$this->print_debug("User removed from member group.");
			}
			else $this->print_debug("User not in member group. No action required.");
		}
		// reader check (read only)
		if ( in_array($username,$this->readers) )
		{
			if ( !in_array("reader",$current_user_groups) ) {
				$this->current_user->addGroup("reader");
				$this->print_debug("User added to reader group.");
			}
			else $this->print_debug("User is already in reader group. No action required.");
		}
		// remove from reader group
		else
		{
			if ( in_array("reader",$current_user_groups) ) {
				$this->current_user->removeGroup("reader");
				$this->print_debug("User removed from reader group.");
			}
			else $this->print_debug("User not in reader group. No action required.");
		}
		
		$this->print_debug("Returning to MediaWiki control.");
	}
	
	private function print_debug($debug_text,$debug_array = NULL)
	{
		global $debug;
		$debug = TRUE;
		if ( isset($array) ) $text = $debug_text." ".implode("::",$debug_array);
		else $text = $debug_text;
		wfDebugLog("UserRightsElevation",$text,TRUE);
	}
	
}

// run user elevation
function run_user_elevation($user)
{
	$user_elevation = new UserRightsElevation($user);
	$user_elevation->manage_rights();
	return TRUE;
}

// register global hook
global $wgHooks;
$wgHooks['UserLoadAfterLoadFromSession'][] = 'run_user_elevation';


###################################################################
# SECURITY SETTINGS
###################################################################

// setup custom namespaces
define("NS_UM",500);
define("NS_MEMBERS",502);
define("NS_ADMINS",504);
define("NS_OFFICERS",550);
global $wgExtraNamespaces;
$wgExtraNamespaces = array(
		NS_UM => "UM",
		NS_UM+1 => "UM_Talk",
		NS_MEMBERS => "Members",
		NS_MEMBERS+1 => "Members_Talk",
		NS_ADMINS => "Admins",
		NS_ADMINS+1 => "Admins_Talk",
		NS_OFFICERS => "Officers",
		NS_OFFICERS+1 => "Officers_Talk"
	);
	
// setup namespace edit permission keywords
global $wgNamespaceProtection;
$wgNamespaceProtection[NS_UM] = array("ns-um-edit");
$wgNamespaceProtection[NS_UM+1] = array("ns-um-talk-edit");
$wgNamespaceProtection[NS_MEMBERS] = array("ns-members-edit");
$wgNamespaceProtection[NS_MEMBERS+1] = array("ns-members-talk-edit");
$wgNamespaceProtection[NS_ADMINS] = array("ns-admins-edit");
$wgNamespaceProtection[NS_ADMINS+1] = array("ns-admins-talk-edit");
$wgNamespaceProtection[NS_OFFICERS] = array("ns-officers-edit");
$wgNamespaceProtection[NS_OFFICERS+1] = array("ns-officers-talk-edit");

// import security settings
global $access_anonymous_read, $access_anonymous_edit;
if ( !isset($access_anonymous_read) OR $access_anonymous_read != FALSE ) $access_anonymous_read = TRUE;
if ( !isset($access_anonymous_edit) OR $access_anonymous_edit != TRUE ) $access_anonymous_edit = FALSE;
global $access_um_read, $access_um_edit;
if ( !isset($access_um_read) OR $access_um_read != FALSE ) $access_um_read = TRUE;
if ( !isset($access_um_edit) OR $access_um_edit != TRUE ) $access_um_edit = FALSE;

//  setup group permissions
global $wgGroupPermissions;
$wgGroupPermissions["*"]["createaccount"] = FALSE;
$wgGroupPermissions["*"]["read"] = $access_anonymous_read;
$wgGroupPermissions["*"]["edit"] = $access_anonymous_edit;
$wgGroupPermissions["*"]["ns-um-edit"] = FALSE;
$wgGroupPermissions["*"]["ns-um-talk-edit"] = FALSE;
$wgGroupPermissions["*"]["ns-members-edit"] = FALSE;
$wgGroupPermissions["*"]["ns-members-talk-edit"] = FALSE;
$wgGroupPermissions["*"]["ns-admins-edit"] = FALSE;
$wgGroupPermissions["*"]["ns-admins-talk-edit"] = FALSE;
$wgGroupPermissions["*"]["ns-officers-edit"] = FALSE;
$wgGroupPermissions["*"]["ns-officers-talk-edit"] = FALSE;

$wgGroupPermissions["user"]["read"] = $access_um_read;	
$wgGroupPermissions["user"]["edit"] = $access_um_edit;
$wgGroupPermissions["user"]["upload"] = $access_um_edit;
$wgGroupPermissions["user"]["writeapi"] = FALSE;
$wgGroupPermissions["user"]["ns-um-edit"] = TRUE;
$wgGroupPermissions["user"]["ns-um-talk-edit"] = TRUE;

$wgGroupPermissions["reader"]["read"] = TRUE;
$wgGroupPermissions["reader"]["edit"] = FALSE;
$wgGroupPermissions["reader"]["upload"] = FALSE;

$wgGroupPermissions["member"] = $wgGroupPermissions["user"];
$wgGroupPermissions["member"]["read"] = TRUE;			
$wgGroupPermissions["member"]["edit"] = TRUE;
$wgGroupPermissions["member"]["upload"] = TRUE;
$wgGroupPermissions["member"]["ns-members-edit"] = TRUE;
$wgGroupPermissions["member"]["ns-members-talk-edit"] = TRUE;

$wgGroupPermissions["sysop"]["read"] = TRUE;			
$wgGroupPermissions["sysop"]["edit"] = TRUE;
$wgGroupPermissions["sysop"]["createaccount"] = FALSE;
$wgGroupPermissions["sysop"]["ns-admins-edit"] = TRUE;
$wgGroupPermissions["sysop"]["ns-admins-talk-edit"] = TRUE;
$wgGroupPermissions["sysop"]["ns-officers-edit"] = TRUE;
$wgGroupPermissions["sysop"]["ns-officers-talk-edit"] = TRUE;
$wgGroupPermissions["sysop"]["editinterface"] = TRUE;

$wgGroupPermissions["bureaucrat"]["userrights"] = FALSE;


###################################################################
# SKIN CONTROL
###################################################################

function update_sidebar($skin,&$bar)
{
	$eecs = "<div class='pbody'>
				<ul>
					<li><a href='http://www.umich.edu'>UM Website</a></li>
					<li><a href='http://www.eecs.umich.edu'>EECS Website</a></li>
					<li><a href='http://www.eecs.umich.edu/dco'>DCO Website</a></li>
					
				</ul>
				</div>";
	$bar["EECS @ UM"] = $eecs;
	global $shortname;
	$tools = "<div class='pbody'>
				<ul>
					<li><a href='http://www.eecs.umich.edu/dco/contact.php'>Contact DCO</a></li>
					<li><a href='http://www.mediawiki.org/wiki/Help:Formatting'>Wiki Syntax</a></li>
				</ul>
				</div>";
	$bar["Tools"] = $tools;
	return TRUE;
}

global $wgHooks;
$wgHooks['SkinBuildSidebar'][] = 'update_sidebar';

###################################################################
# OTHER ENVIRONMENT MODIFICATIONS
###################################################################

// disable cache based on config variable
function disable_cache(&$parser,&$text)
{
	global $wgOut, $disable_cache;
	if ( isset($disable_cache) AND $disable_cache === TRUE ) 
	{
		$parser->disableCache();
		$wgOut->enableClientCache(FALSE);
	}
	return TRUE;
}

// register global hook
global $wgHooks;
$wgHooks['ParserBeforeTidy'][] = 'disable_cache';


?>