<?php /* Smarty version 2.6.26, created on 2011-08-10 18:59:25
         compiled from MultiSites/templates/row.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'MultiSites/templates/row.tpl', 5, false),)), $this); ?>
<td class="multisites-label label" >
    <a title="View reports" href="index.php?module=CoreHome&action=index&date=%date%&period=%period%&idSite=%idsite%">%name%</a>
    
    <span style="width: 10px; margin-left:3px"> 
	<a target="_blank" title="<?php echo ((is_array($_tmp='General_GoTo')) ? $this->_run_mod_handler('translate', true, $_tmp, "%main_url%") : smarty_modifier_translate($_tmp, "%main_url%")); ?>
" href="%main_url%"><img src="plugins/MultiSites/images/link.gif" /></a>
    </span>
</td>
<td class="multisites-column">
    %visits%
</td>
<td class="multisites-column">
    %actions%
</td>
<?php if ($this->_tpl_vars['displayRevenueColumn']): ?>
<td class="multisites-column">
    %revenue%
</td>
<?php endif; ?>
<?php if ($this->_tpl_vars['period'] != 'range'): ?>
	<td style="width:170px">
	    <div class="visits" style="display:none">%visitsSummary%</div>
	    <div class="actions"style="display:none">%actionsSummary%</div>
		<?php if ($this->_tpl_vars['displayRevenueColumn']): ?>
	    <div class="revenue"style="display:none">%revenueSummary%</div>
	    <?php endif; ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['show_sparklines']): ?>
<td style="width:180px">
    <div id="sparkline_%idsite%" style="width: 100px; margin: auto">
	%sparkline%
    </div>
</td>
<?php endif; ?>