<?php
	global $CONFIG;
	if(!$vars['entity']->ftp_remote_host_port) $vars['entity']->ftp_remote_host_port = 21;
	if(!$vars['entity']->ftp_remote_host_timeout) $vars['entity']->ftp_remote_host_timeout = 90;
?>
<p>
	<table><tr><td>
	<?php echo elgg_echo("elggbackup:settings:scheduler");?><br>
	<select name="params[scheduleractive]">
		<option value="yes" <?php if ($vars['entity']->scheduleractive == 'yes') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:yes'); ?></option>
		<option value="no" <?php if ($vars['entity']->scheduleractive != 'yes') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('option:no'); ?></option>
	</select>
	<br>
	<?php echo elgg_echo("elggbackup:settings:frequency");?><br>
		
	<select name="params[frequency]">
		<option value="daily" <?php if ($vars['entity']->frequency == 'daily') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('daily'); ?></option>
		<option value="weekly" <?php if ($vars['entity']->frequency == 'weekly') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('weekly'); ?></option>
		<option value="monthly" <?php if ($vars['entity']->frequency == 'monthly') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('monthly'); ?></option>
	</select>
	<br>
	
	<?php echo elgg_echo("elggbackup:settings:type");?><br>
	<select name="params[backup_type]">
		<option value="email" <?php if ($vars['entity']->backup_type == 'email') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('elggbackup:settings:type:email'); ?></option>
		<option value="ftp" <?php if ($vars['entity']->backup_type == 'ftp') echo " selected=\"yes\" "; ?>><?php echo elgg_echo('elggbackup:settings:type:ftp'); ?></option>
	</select>
	<br>
	<?php echo elgg_echo("elggbackup:settings:email");?><br>
	
	<?php if(!$vars['entity']->emailto) $vars['entity']->emailto = $CONFIG->siteemail;?>
	<input type="text" name="params[emailto]" value="<?php echo $vars['entity']->emailto;?>"/>
	</td><td>
		<input type="button" value="<?php echo elgg_echo("elggbackup:settings:manualbackup");?>" <?php if(!isset($vars['entity']->scheduleractive)) echo "disabled";?> onclick="document.location.href='<?php echo $CONFIG->wwwroot?>pg/elggbackup/index.php'"><br>
		<?php echo elgg_echo("elggbackup:settings:manualbackupinfo");?>
		<br />
		<input type="button" value="<?php echo elgg_echo("elggbackup:settings:backuptest");?>" <?php if(!isset($vars['entity']->scheduleractive)) echo "disabled";?> onclick="document.location.href='<?php echo $CONFIG->wwwroot?>pg/elggbackup/index.php?test=yes'"><br>
		<?php echo elgg_echo("elggbackup:settings:backuptestinfo");?>
		
	</td>
	</tr></table>
	<h3 class="settings"><?php echo elgg_echo("elggbackup:settings:ftp")?></h3>
	<table>
	<tr><td><?php echo elgg_echo("elggbackup:settings:ftp:host")?></td><td><input type="text" name="params[ftp_remote_host]" value="<?php echo $vars['entity']->ftp_remote_host;?>"/></td></tr>
	<tr><td><?php echo elgg_echo("elggbackup:settings:ftp:port")?></td><td><input type="text" name="params[ftp_remote_host_port]" value="<?php echo $vars['entity']->ftp_remote_host_port;?>"/></td></tr>
	<tr><td><?php echo elgg_echo("elggbackup:settings:ftp:timeout")?></td><td><input type="text" name="params[ftp_remote_host_timeout]" value="<?php echo $vars['entity']->ftp_remote_host_timeout;?>"/></td></tr>	
	<tr><td><?php echo elgg_echo("elggbackup:settings:ftp:user")?></td><td><input type="text" name="params[ftp_user]" value="<?php echo $vars['entity']->ftp_user;?>"/></td></tr>
	<tr><td><?php echo elgg_echo("elggbackup:settings:ftp:password")?></td><td><input type="text" name="params[ftp_password]" value="<?php echo $vars['entity']->ftp_password;?>"/></td></tr>
	<tr><td><?php echo elgg_echo("elggbackup:settings:ftp:remote_folder")?></td><td><input type="text" name="params[ftp_remote_folder]" value="<?php echo $vars['entity']->ftp_remote_folder;?>"/></td></tr>
	</table>	
</p>