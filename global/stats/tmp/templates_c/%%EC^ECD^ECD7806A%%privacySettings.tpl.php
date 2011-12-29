<?php /* Smarty version 2.6.26, created on 2011-08-10 18:58:34
         compiled from /y/wiki/global/stats/plugins/PrivacyManager/templates/privacySettings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', '/y/wiki/global/stats/plugins/PrivacyManager/templates/privacySettings.tpl', 7, false),array('modifier', 'inlineHelp', '/y/wiki/global/stats/plugins/PrivacyManager/templates/privacySettings.tpl', 28, false),array('modifier', 'escape', '/y/wiki/global/stats/plugins/PrivacyManager/templates/privacySettings.tpl', 148, false),array('function', 'url', '/y/wiki/global/stats/plugins/PrivacyManager/templates/privacySettings.tpl', 11, false),)), $this); ?>
<?php $this->assign('showSitesSelection', false); ?>
<?php $this->assign('showPeriodSelection', false); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['isSuperUser']): ?>

<h2><?php echo ((is_array($_tmp='PrivacyManager_TeaserHeadline')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<p><?php echo ((is_array($_tmp='PrivacyManager_Teaser')) ? $this->_run_mod_handler('translate', true, $_tmp, '<a href="#anonymizeIPAnchor">', "</a>", '<a href="#deleteLogsAnchor">', "</a>", '<a href="#optOutAnchor">', "</a>") : smarty_modifier_translate($_tmp, '<a href="#anonymizeIPAnchor">', "</a>", '<a href="#deleteLogsAnchor">', "</a>", '<a href="#optOutAnchor">', "</a>")); ?>
</p>
<a name="anonymizeIPAnchor"></a>
<h2><?php echo ((is_array($_tmp='PrivacyManager_UseAnonymizeIp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<form method="post" action="<?php echo smarty_function_url(array('action' => 'index','form' => 'formMaskLength'), $this);?>
" id="formMaskLength" name="formMaskLength">
	<div id='anonymizeIpSettings'>
		<table class="adminTable" style='width:800px;'>
			<tr>
				<td width="250"><?php echo ((is_array($_tmp='PrivacyManager_UseAnonymizeIp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<br/>
					<span class="form-description"><?php echo ((is_array($_tmp='PrivacyManager_AnonymizeIpDescription')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
				</td>
				<td width='500'>
					<label><input type="radio" name="anonymizeIPEnable" value="1" <?php if ($this->_tpl_vars['anonymizeIP']['enabled'] == '1'): ?>
								  checked <?php endif; ?>/> <?php echo ((is_array($_tmp='General_Yes')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label>
					<label><input type="radio" name="anonymizeIPEnable" value="0"
								  style="margin-left:20px;" <?php if ($this->_tpl_vars['anonymizeIP']['enabled'] == '0'): ?> checked <?php endif; ?>/>  <?php echo ((is_array($_tmp='General_No')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

					</label>
					<input type="hidden" name="token_auth" value="<?php echo $this->_tpl_vars['token_auth']; ?>
"/>
					<input type="hidden" name="pluginName" value="<?php echo $this->_tpl_vars['anonymizeIP']['name']; ?>
"/>
				</td>
				<td width="200">
					<?php echo ((is_array($_tmp=((is_array($_tmp='AnonymizeIP_PluginDescription')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('inlineHelp', true, $_tmp) : smarty_modifier_inlineHelp($_tmp)); ?>

				</td>
			</tr>
		</table>
	</div>
	<div id="anonymizeIPenabled">
		<table class="adminTable">
			<tr>
				<td width="250"><?php echo ((is_array($_tmp='PrivacyManager_AnonymizeIpMaskLengtDescription')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
				<td>
					<label><input type="radio" name="maskLength" value="1" <?php if ($this->_tpl_vars['anonymizeIP']['maskLength'] == '1'): ?>
								  checked <?php endif; ?>/> <?php echo ((is_array($_tmp='PrivacyManager_AnonymizeIpMaskLength')) ? $this->_run_mod_handler('translate', true, $_tmp, '1', "192.168.100.xxx") : smarty_modifier_translate($_tmp, '1', "192.168.100.xxx")); ?>
 <?php echo ((is_array($_tmp='General_Recommended')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

					</label><br/>
					<label><input type="radio" name="maskLength" value="2" <?php if ($this->_tpl_vars['anonymizeIP']['maskLength'] == '2'): ?>
								  checked <?php endif; ?>/> <?php echo ((is_array($_tmp='PrivacyManager_AnonymizeIpMaskLength')) ? $this->_run_mod_handler('translate', true, $_tmp, '2', "192.168.xxx.xxx") : smarty_modifier_translate($_tmp, '2', "192.168.xxx.xxx")); ?>
</label><br/>
					<label><input type="radio" name="maskLength" value="3" <?php if ($this->_tpl_vars['anonymizeIP']['maskLength'] == '3'): ?>
								  checked <?php endif; ?>/> <?php echo ((is_array($_tmp='PrivacyManager_AnonymizeIpMaskLength')) ? $this->_run_mod_handler('translate', true, $_tmp, '3', "192.xxx.xxx.xxx") : smarty_modifier_translate($_tmp, '3', "192.xxx.xxx.xxx")); ?>
</label>
				</td>
			</tr>
		</table>
	</div>
	<input type="submit" value="<?php echo ((is_array($_tmp='General_Save')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" id="privacySettingsSubmit" class="submit"/>
</form>

<a name="deleteLogsAnchor"></a>
<h2><?php echo ((is_array($_tmp='PrivacyManager_DeleteLogSettings')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<p><?php echo ((is_array($_tmp='PrivacyManager_DeleteLogDescription')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>
<form method="post" action="<?php echo smarty_function_url(array('action' => 'index','form' => 'formDeleteSettings'), $this);?>
" id="formDeleteSettings" name="formMaskLength">
	<div id='deleteLogSettingEnabled'>
		<table class="adminTable" style='width:800px;'>
			<tr>
				<td width="250"><?php echo ((is_array($_tmp='PrivacyManager_UseDeleteLog')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<br/>

				</td>
				<td width='500'>
					<label><input type="radio" name="deleteEnable" value="1" <?php if ($this->_tpl_vars['deleteLogs']['config']['delete_logs_enable'] == '1'): ?>
								  checked <?php endif; ?>/> <?php echo ((is_array($_tmp='General_Yes')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label>
					<label><input type="radio" name="deleteEnable" value="0"
								  style="margin-left:20px;" <?php if ($this->_tpl_vars['deleteLogs']['config']['delete_logs_enable'] == '0'): ?>
								  checked <?php endif; ?>/>  <?php echo ((is_array($_tmp='General_No')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

					</label>
					<span class="ajaxSuccess">
						<?php echo ((is_array($_tmp='PrivacyManager_DeleteLogDescription2')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

						<a href="http://piwik.org/faq/general/#faq_125" target="_blank">
							<?php echo ((is_array($_tmp='General_ClickHere')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

						</a>
					</span>
				</td>
				<td width="200">
					<?php echo ((is_array($_tmp=((is_array($_tmp='PrivacyManager_DeleteLogInfo')) ? $this->_run_mod_handler('translate', true, $_tmp, $this->_tpl_vars['deleteLogs']['deleteTables']) : smarty_modifier_translate($_tmp, $this->_tpl_vars['deleteLogs']['deleteTables'])))) ? $this->_run_mod_handler('inlineHelp', true, $_tmp) : smarty_modifier_inlineHelp($_tmp)); ?>

				</td>
			</tr>
		</table>
	</div>

	<div id="deleteLogSettings">
		<table class="adminTable" style='width:800px;'>
			<tr>
				<td width="250">&nbsp;</td>
				<td width="500">
					<label><?php echo ((is_array($_tmp='PrivacyManager_DeleteLogsOlderThan')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

						<input type="text" id="deleteOlderThan" value="<?php echo $this->_tpl_vars['deleteLogs']['config']['delete_logs_older_than']; ?>
" style="width:30px;"
							   name="deleteOlderThan">
						<?php echo ((is_array($_tmp='CoreHome_PeriodDays')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br/>
					<span class="form-description"><?php echo ((is_array($_tmp='PrivacyManager_LeastDaysInput')) ? $this->_run_mod_handler('translate', true, $_tmp, '7') : smarty_modifier_translate($_tmp, '7')); ?>
</span>
				</td>
				<td width="200">

				</td>
			</tr>
			<tr>
				<td width="250">&nbsp;</td>
				<td width="500">
					<?php echo ((is_array($_tmp='PrivacyManager_DeleteLogInterval')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

					<select id="deleteLowestInterval" name="deleteLowestInterval">
						<option <?php if ($this->_tpl_vars['deleteLogs']['config']['delete_logs_schedule_lowest_interval'] == '1'): ?> selected="selected" <?php endif; ?>
																									value="1"> <?php echo ((is_array($_tmp='CoreHome_PeriodDay')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</option>
						<option <?php if ($this->_tpl_vars['deleteLogs']['config']['delete_logs_schedule_lowest_interval'] == '7'): ?> selected="selected" <?php endif; ?>
																									value="7"><?php echo ((is_array($_tmp='CoreHome_PeriodWeek')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</option>
						<option <?php if ($this->_tpl_vars['deleteLogs']['config']['delete_logs_schedule_lowest_interval'] == '30'): ?> selected="selected" <?php endif; ?>
																									 value="30"><?php echo ((is_array($_tmp='CoreHome_PeriodMonth')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</option>
					</select>
				</td>
				<td width="200">
					<?php ob_start(); ?>
						<?php if ($this->_tpl_vars['deleteLogs']['lastRun']): ?><strong><?php echo ((is_array($_tmp='PrivacyManager_LastDelete')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:</strong>
							<?php echo $this->_tpl_vars['deleteLogs']['lastRunPretty']; ?>

							<br/><br/><?php endif; ?>
						<strong><?php echo ((is_array($_tmp='PrivacyManager_NextDelete')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:</strong>
						<?php echo $this->_tpl_vars['deleteLogs']['nextRunPretty']; ?>

					<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('purgeStats', ob_get_contents());ob_end_clean(); ?>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['purgeStats'])) ? $this->_run_mod_handler('inlineHelp', true, $_tmp) : smarty_modifier_inlineHelp($_tmp)); ?>

				</td>
			</tr>
			<tr>
				<td width="250">&nbsp;</td>
				<td width="500">
					<?php echo ((is_array($_tmp='PrivacyManager_DeleteMaxRows')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 
					<select id="deleteMaxRows" name="deleteMaxRows">
						<option <?php if ($this->_tpl_vars['deleteLogs']['config']['delete_max_rows_per_run'] == '100'): ?> selected="selected" <?php endif; ?>  value="100">100.000</option>
						<option <?php if ($this->_tpl_vars['deleteLogs']['config']['delete_max_rows_per_run'] == '500'): ?> selected="selected" <?php endif; ?> value="500">500.000</option>
						<option <?php if ($this->_tpl_vars['deleteLogs']['config']['delete_max_rows_per_run'] == '1000'): ?> selected="selected" <?php endif; ?> value="1000">1.000.000</option>
					</select>
				</td>
				<td width="200"></td>
			</tr>
		</table>
	</div>
	<input type="submit" value="<?php echo ((is_array($_tmp='General_Save')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" id="deleteLogSettingsSubmit" class="submit"/>
</form>

<?php endif; ?>

<a name="optOutAnchor"></a>
<h2><?php echo ((is_array($_tmp='CoreAdminHome_OptOutForYourVisitors')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<p><?php echo ((is_array($_tmp='CoreAdminHome_OptOutExplanation')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

<?php ob_start(); ?><?php echo $this->_tpl_vars['piwikUrl']; ?>
index.php?module=CoreAdminHome&action=optOut&language=<?php echo $this->_tpl_vars['language']; ?>
<?php $this->_smarty_vars['capture']['optOutUrl'] = ob_get_contents(); ob_end_clean(); ?>
<?php $this->assign('optOutUrl', $this->_smarty_vars['capture']['optOutUrl']); ?>
<?php ob_start(); ?>
<iframe frameborder="no" width="600px" height="200px" src="<?php echo $this->_smarty_vars['capture']['optOutUrl']; ?>
"></iframe><?php $this->_smarty_vars['capture']['iframeOptOut'] = ob_get_contents(); ob_end_clean(); ?>
<code><?php echo ((is_array($_tmp=$this->_smarty_vars['capture']['iframeOptOut'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</code>
<br/>
<?php echo ((is_array($_tmp='CoreAdminHome_OptOutExplanationBis')) ? $this->_run_mod_handler('translate', true, $_tmp, "<a href='".($this->_tpl_vars['optOutUrl'])."' target='_blank'>", "</a>") : smarty_modifier_translate($_tmp, "<a href='".($this->_tpl_vars['optOutUrl'])."' target='_blank'>", "</a>")); ?>

</p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>