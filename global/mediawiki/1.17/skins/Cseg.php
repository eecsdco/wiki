<?php
/**
 * Custom CSEG skin for Wiki
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/** */
require_once( dirname(__FILE__) . '/MonoBook.php' );

/**
 * @todo document
 * @addtogroup Skins
 */
class SkinCseg extends SkinTemplate {
	function initPage( &$out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'cseg';
		$this->stylename = 'cseg';
		$this->template  = 'CsegTemplate';
	}
}


class CsegTemplate extends QuickTemplate {
	function execute() {
		global $wgUser;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
  "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title><?php $this->text('pagetitle') ?></title>
  <link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath') ?>/common/shared.css">
  <link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath') ?>/cseg/main.css">
  <!--[if IE]>
  <link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath') ?>/cseg/ie.css">
  <script type="text/javascript" src="<?php $this->text('stylepath') ?>/common/IEFixes.js"></script>
  <meta http-equiv="imagetoolbar" content="no" />
  <![endif]-->
<?php if($this->data['jsvarurl'  ]) { ?>  <script type="text/javascript" src="<?php $this->text('jsvarurl'  ) ?>"></script>
<?php } ?>
  <script type="text/javascript" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js"></script>
<?php if($this->data['usercss'   ]) { ?>  <style type="text/css"><?php              $this->html('usercss'   ) ?></style>
<?php } ?>
<?php if($this->data['userjs'    ]) { ?>  <script type="text/javascript" src="<?php $this->text('userjs'    ) ?>"></script>
<?php } ?>
<?php if($this->data['userjsprev']) { ?>  <script type="text/javascript"><?php      $this->html('userjsprev') ?></script>
<?php } ?>
</head>

<body><div id="page">

<div id="header" style="position:relative">
<a href="http://cseg.eecs.umich.edu/">
<img id="banner" alt="CSEG" src="<?php $this->text('stylepath') ?>/cseg/images/title.png" height="100" width="500"></a></div>

<div id="nav"><ul>
  <li><a href="http://cseg.eecs.umich.edu/">Home</a></li>
  <li><a href="http://cseg.eecs.umich.edu/student.html">Student Resources</a></li>
  <li><a href="/cseg/index.php/Links">Links</a></li>
  <li><a href="/cseg/index.php/Current_Officers">Organization</a></li>
  <li><a href="http://cseg.eecs.umich.edu/contact.html">Contact</a></li>
  <li id="officers"><a href="/cseg/index.php/Officers:Main">Officers' Main</a></li>
</ul></div>

<div id="portlets" <?php if($wgUser->getID() == 0) { echo 'class="anon"';}?>>
<?php	if($wgUser->getID() != 0) {
		$this->portlet('p-actions', 'views', 'ca', 'content_actions');
  	}
	$this->portlet('p-personal', 'personaltools', 'pt', 'personal_urls');
?>
</div>

<div id="content">

<div id="port-in">
<?php if($wgUser->getID() != 0) {
  $this->nav();
  $this->toolbox();
} ?>
</div>

<a name="top" id="top"></a>
<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
<h1 class="firstHeading"><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></h1>
<div id="bodyContent">
  <h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
  <div id="contentSub"><?php $this->html('subtitle') ?></div>
  <?php if($this->data['undelete']) { ?><div id="contentSub2"><?php     $this->html('undelete') ?></div><?php } ?>
  <?php if($this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
  <?php if($this->data['showjumplinks']) { ?><div id="jump-to-nav"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div><?php } ?>
<!-- start content -->
<?php $this->html('bodytext') ?>
<!-- end content -->
  <div class="visualClear"></div>
</div>

</div>

<hr class="hidden">

<br style="clear: both;">

<div id="footer">
Webmaster
(<a href="mailto:csegweb%20at%20eecs.umich.edu">csegweb at eecs.umich.edu</a>)
</div>

</div></body></html>
<?php
	}

	function portlink($link, $key, $text="") {
		global $wgUser; ?>
    <li id="<?php echo htmlspecialchars($key) ?>"<?php
		if($link['class']) { ?> class="<?php echo htmlspecialchars($link['class']) ?>"<?php }
		?>><a href="<?php echo htmlspecialchars($link['href']) ?>"><?php
		if($text=="") {
			echo htmlspecialchars($link['text']);
		} else {
			$this->msg($text);
		}  ?></a></li>
<?php
	}

	function portlet($id, $name, $prefix, $data_key) {
		global $wgUser; ?>
<div class="portlet" id="<?php echo $id?>">
  <h5><?php $this->msg($name) ?></h5>
  <div class="pBody"><ul>
<?php		foreach($this->data[$data_key] as $key => $tab) {
			$this->portlink($tab, $prefix.'-'.$key);
		} ?>
  </ul></div>
</div>
<?php
	}

	function nav() {
		global $wgUser; ?>
<div class="portlet" id="p-nav">
  <h5><?php $this->msg('navigation') ?></h5>
  <div class="pBody"><ul>
<?php		foreach($this->data['navigation_urls'] as $navlink) {
			$this->portlink($navlink, $navlink['id']);
		}
?>
  </ul></div>
</div>
<?php
	}

	function toolbox() {
		global $wgUser;
		$nav = $this->data['nav_urls'];
		$skin = $wgUser->getSkin(); ?>
<div class="portlet" id="p-tb">
  <h5><?php $this->msg('toolbox') ?></h5>
  <div class="pBody"><ul>
<?php		if($this->data['notspecialpage']) {
			$this->portlink($nav['whatlinkshere'],'t-whatlinkshere', 'whatlinkshere');
			if($nav['recentchangeslinked']) {
				$this->portlink($nav['recentchangeslinked'], 't-recentchangeslinked',
						'recentchangeslinked');
			}
		}
		if(isset($nav['trackbacklink'])) {
			$this->portlink($nav['trackbacklink'], 't-trackbacklink', 'trackbacklink');
		} 
		if ($this->data['feeds']) { ?>
    <li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
      ?><span id="feed-<?php echo htmlspecialchars($key) ?>"><a href="<?php
      echo htmlspecialchars($feed['href']) ?>"><?php
      echo htmlspecialchars($feed['text']) ?></a>&nbsp;</span><?php } ?></li>
<?php		}

		foreach( array('contributions', 'log',  'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {
			if($nav[$special]) {
				$this->portlink($nav[$special], 't-'.$special, $special);
			}
		}

		if(!empty($nav['print']['href'])) {
			$this->portlink($nav['print'], 't-print', 'printableversion');
		}

		if(!empty($nav['permalink']['href'])) {
			$this->portlink($nav['permalink'], 't-permalink', 'permalink');
		} elseif ($nav['permalink']['href'] === '') { ?>
			<li id="t-ispermalink"<?php echo $skin->tooltip('t-ispermalink'); ?>><?php
				  $this->msg('permalink'); ?></li>
<?php		}

		wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
?>
  </ul></div>
</div>
<?php
	}

}
