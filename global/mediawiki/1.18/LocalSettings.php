<?php

#########################################################################
# DCO WIKI SERVER LOCAL CONFIGURATION FILE
# Changes made here will only effect the local wiki instance
#########################################################################

// For the most part, changes should not be made to this file.
// Most settings are designed to be loaded dynamicly from the wiki
// management class or global config file.

// Author: Matt Colf mcolf@umich.edu
// Disclaimer: It's not perfect, but neither are you. 

$upgrade_running = FALSE;

// fake user account for testing purposes
if ( isset($_SERVER['REMOTE_USER'] ) ) {
	if ( $_SERVER['REMOTE_USER'] == "mcolf" ) {
		//$_SERVER['REMOTE_USER'] = "rwcohn";
	}
}

// Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

// load global configuration
$global_path 					= "/y/wiki/global";
require_once("$global_path/config.php");
if ( !$upgrade_running ) {
	require_once("$global_path/management/wiki_management.class.php");
	$manage = new EECS_WIKI_MANAGEMENT();
}

// error settings
global $global_debug;
if ( $global_debug ) $wgShowExceptionDetails = TRUE;

// figure out short name
$shortname 						= $manage->wiki_shortname_get();

# MAINTENANCE OPTIONS
# options that allow temporary maintenance work

global $global_read_only, $global_notice;
$wgReadOnly 					= $global_read_only;
$wgSiteNotice 					= $global_notice;


# SET GROUP MEMBERSHIPS
# dynamically load group memberships from database

$admins_local 					= $manage->wiki_users_get($shortname,"admins");
$members_local 					= $manage->wiki_users_get($shortname,"members");
$readers_local 					= $manage->wiki_users_get($shortname,"readers");
$blacklist_local 				= $manage->wiki_users_get($shortname,"blacklist");
$access_anonymous_read 			= $manage->wiki_option_get($shortname,"access_anonymous_read");
$access_anonymous_edit 			= $manage->wiki_option_get($shortname,"access_anonymous_edit");
$access_um_read 				= $manage->wiki_option_get($shortname,"access_um_read");
$access_um_edit 				= $manage->wiki_option_get($shortname,"access_um_edit");


# INSTANCE SETTINGS
# The following settings should be modified for each wiki instance.

// wiki title		
$wgSitename						= $manage->wiki_option_get($shortname,"fullname");

// default skin
$wgDefaultSkin 					= $manage->wiki_option_get($shortname,"default_skin");
$wgSkipSkins					= array("monobook");

// path information
$local_data_path 				= $manage->wiki_option_get($shortname,"local_data_path"); 
$wgScriptPath 					= "/$shortname"; 
$wgStylePath        			= "/$shortname/skins"; 
$wgUsePathInfo 					= TRUE; 

// wiki logo
global $global_logo;
$wgLogo							= "/$shortname/skins/common/images/logo.png";

// databse configuration
$wgDBtype           			= $manage->wiki_option_get($shortname,"db_type"); 
$wgDBserver         			= $manage->wiki_option_get($shortname,"db_server"); 
$wgDBname           			= $manage->wiki_option_get($shortname,"db_name"); 
$wgDBuser           			= $manage->wiki_option_get($shortname,"db_user"); 
$wgDBpassword       			= $manage->wiki_option_get($shortname,"db_password"); 

// email settings
$wgEnableEmail      			= TRUE;
$wgEnableUserEmail  			= TRUE;
$wgEmergencyContact 			= "help@eecs.umich.edu";
$wgPasswordSender   			= "help@eecs.umich.edu";

// notification settings
$wgEnotifUserTalk      			= TRUE;
$wgEnotifWatchlist     			= TRUE;
$wgEmailAuthentication 			= TRUE;

// cache
$wgMainCacheType    			= CACHE_NONE;
$wgMemCachedServers 			= array();
$wgCacheDirectory 				= FALSE; 
$disable_cache 					= TRUE;			# for debugging
$wgCachePages					= FALSE;

// uploads
ini_set( 'memory_limit', '30M' );
ini_set( 'post_max_size', '24M' );
ini_set( 'upload_max_filesize', '24M' );
$wgEnableUploads  				= TRUE;
$wgUploadDirectory				= "$local_data_path/images";
$wgUploadPath					= "/global/data/$shortname/images";
$wgCheckFileExtensions			= FALSE;
$wgStrictFileExtensions			= FALSE;
$wgFileExtensions				= array('png','gif','jpg','jpeg','doc','xls','pdf','ppt','tiff','bmp','docx', 'xlsx', 'pptx','ps','odt','ods','odp','odg','svg');
$wgUseImageMagick 				= TRUE; 
$wgSVGConverter 				= 'ImageMagick'; 
$wgImageMagickConvertCommand 	= "/usr/bin/convert";
$wgShellLocale 					= "en_US.utf8";
$wgFileBlacklist 				= array();
$wgMimeTypeBlacklist			= array();


// keys (DO NOT MODIFY!)
$wgSecretKey 					= "805e70507482e447a0a346f2e7102e5263ff89ad8c211f2b652bc87561fb8600";
$wgUpgradeKey 					= "723fb01bb7a58997";

// other settings
$wgMetaNamespace				= "$shortname-wiki";	
$wgUseTeX           			= TRUE;
$wgDiff3 						= "/usr/bin/diff3";
$wgResourceLoaderMaxQueryLength = 512;
$wgScriptExtension  			= ".php";
$wgLanguageCode 				= "en";
$wgAmericanDates				= TRUE;
$wgLocaltimezone				= "America/Detroit";
$wgBreakFrames					= FALSE;
$wgWhitelistRead				= array("Special:Userlogin","-","MediaWiki:Common.css");
$wgEnableAPI					= FALSE;
$wgDebugLogFile 				= "$local_data_path/debug.log";

# EXTENSIONS
# Load any required extension here. Don't load during upgrade operations.

if ( !$upgrade_running ) {

	// eecs cosign authentication and automatic account creation
	require_once("extensions/eecs_environment.php");

	// captcha 
	require_once("extensions/ConfirmEdit/ConfirmEdit.php");
	$wgCaptchaClass 				= "SimpleCaptcha";
	$wgGroupPermissions['user']['skipcaptcha'] = true; 
	$wgCaptchaTriggers['edit'] 		= true;

	// pdf embedding
	require_once("extensions/EmbedPDF.php");

	// syntax highlighting
	require_once("extensions/SyntaxHighlight_GeSHi/SyntaxHighlight_GeSHi.php");

	// threaded discussions
	// requires DB update: http://www.mediawiki.org/wiki/User:Akaniji/Extension:LiquidThreads
	//require_once("extensions/LiquidThreads/LiquidThreads.php");

	// vector improvements
	require_once("extensions/Vector/Vector.php");
	$wgDefaultUserOptions['vector-collapsiblenav'] = 1;
	$wgDefaultUserOptions['vector-collapsibletabs'] = 1;
	$wgDefaultUserOptions['vector-editwarning'] = 1;
	$wgDefaultUserOptions['vector-expandablesearch'] = 1;
	$wgDefaultUserOptions['vector-footercleanup'] = 1;
	$wgDefaultUserOptions['vector-simplesearch'] = 1;
	$wgDefaultUserOptions['useeditwarning'] = 1;
	$wgVectorUseSimpleSearch = true;

	// wiki editor
	require_once("extensions/WikiEditor/WikiEditor.php");
	$wgDefaultUserOptions['usebetatoolbar'] = 1;
	$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;
	$wgDefaultUserOptions['wikieditor-preview'] = 0;
	$wgDefaultUserOptions['wikieditor-dialogs'] = 1;
	
	// stats
	require_once("extensions/Piwik/Piwik.php");
	$wgPiwikURL = "wiki.eecs.umich.edu/global/stats/";
	$wgGroupPermissions['sysop']['viewpiwik'] = FALSE;
	$wgPiwikIDSite = "1";
	
	// breaadcrumbs BROKEN IN 1.18
	//$wgUseCategoryBrowser = true;
	//require_once("extensions/BreadCrumbs.php");
	
	// category tree
	$wgUseAjax = true;
	require_once("extensions/CategoryTree/CategoryTree.php");

}

