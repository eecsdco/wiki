<?php /* Smarty version 2.6.26, created on 2011-08-10 18:33:51
         compiled from /y/wiki/global/stats/plugins/CoreHome/templates/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'ajaxLoadingDiv', '/y/wiki/global/stats/plugins/CoreHome/templates/index.tpl', 15, false),array('function', 'ajaxRequestErrorDiv', '/y/wiki/global/stats/plugins/CoreHome/templates/index.tpl', 16, false),)), $this); ?>
<?php $this->assign('showSitesSelection', true); ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if (isset ( $this->_tpl_vars['menu'] ) && $this->_tpl_vars['menu']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/menu.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>

<div class="page">
<div class="pageWrap">
	<div class="nav_sep"></div>
    <div class="top_controls">
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/period_select.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/header_message.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </div>
    
    <?php echo smarty_function_ajaxLoadingDiv(array(), $this);?>

    <?php echo smarty_function_ajaxRequestErrorDiv(array(), $this);?>

    
    <div id="content" class="home">
        <?php if ($this->_tpl_vars['content']): ?><?php echo $this->_tpl_vars['content']; ?>
<?php endif; ?>
    </div>
    <div class="clear"></div>
</div>
</div>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreHome/templates/piwik_tag.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>
</body>
</html>