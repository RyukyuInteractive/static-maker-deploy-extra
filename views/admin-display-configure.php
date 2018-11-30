<?php

$crypto_util = $this->static_maker->crypto_util;
$options = get_option(STATIC_MAKER_DEPLOY_EXTRA);

$remote_host = $options ? $options['remote_host'] ?? '' : '';
$remote_user = $options ? $options['remote_user'] ?? '' : '';
$remote_dir = $options ? $options['remote_dir'] ?? '' : '';
$remote_ssh_key = $options ? $options['remote_ssh_key'] ?? '' : '';
$deploy_notify_email = $options ? $options['deploy_notify_email'] ?? '' : '';
$deploy_notify_email_subject = $options ? $options['deploy_notify_email_subject'] ?? '' : '';
?>
<div class="wrap">

	<h2><?=esc_html(get_admin_page_title());?></h2>

	<?php settings_errors();?>

	<form method="post" name="<?=STATIC_MAKER_DEPLOY_EXTRA?>" action="options.php">

		<?php settings_fields(STATIC_MAKER_DEPLOY_EXTRA)?>
		<?php do_settings_sections(STATIC_MAKER_DEPLOY_EXTRA);?>

		<table class="form-table">
			<tr>
				<th scope="row"><label
						for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-host'?>"><?=__('Remote Host', STATIC_MAKER_DEPLOY_EXTRA)?></label>
				</th>
				<td>
					<input type="text" class="regular-text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-host'?>"
						   name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[remote_host]"
						   placeholder="<?=__('ip, hostname etc..', STATIC_MAKER_DEPLOY_EXTRA)?>"
						   value="<?=$remote_host?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label
						for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-user'?>"><?=__('Remote User', STATIC_MAKER_DEPLOY_EXTRA)?></label>
				</th>
				<td>
					<input type="text" class="regular-text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-user'?>"
						   name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[remote_user]"
						   placeholder="<?=__('ec2-user, centos etc...', STATIC_MAKER_DEPLOY_EXTRA)?>"
						   value="<?=$remote_user?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label
						for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-dir'?>"><?=__('Remote Directory', STATIC_MAKER_DEPLOY_EXTRA)?></label>
				</th>
				<td>
					<input type="text" class="regular-text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-dir'?>"
						   name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[remote_dir]"
						   placeholder="<?=__('/var/www/html etc...', STATIC_MAKER_DEPLOY_EXTRA)?>"
						   value="<?=$remote_dir?>">
				</td>
			</tr>
			<tr>
				<th><?=__('SSH Private Key', STATIC_MAKER_DEPLOY_EXTRA)?></th>
				<td>
					<label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-ssh-key'?>">
						<textarea id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-ssh-key'?>" class="large-text code"
								  name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[remote_ssh_key]'?>"><?=$crypto_util::decrypt($remote_ssh_key, true)?></textarea>
					</label>
				</td>
			</tr>
			<tr>
				<th><?=__('Notification Email', STATIC_MAKER_DEPLOY_EXTRA)?></th>
				<td>
					<label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-deploy-notify-email'?>">
						<input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-deploy-notify-email'?>" class="large-text code"
								  name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[deploy_notify_email]'?>" placeholder="admin@example.com" value="<?=$deploy_notify_email?>">
					</label>
					<p><?=__('if you specify multiple emails, use comma to separate them', STATIC_MAKER_DEPLOY_EXTRA)?></p>
				</td>
			</tr>
			<tr>
				<th><?=__('Notification Subject', STATIC_MAKER_DEPLOY_EXTRA)?></th>
				<td>
					<label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-deploy-notify-email-subject'?>">
						<input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-deploy-notify-email-subject'?>" class="large-text code"
								  name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[deploy_notify_email_subject]'?>" placeholder="" value="<?=$deploy_notify_email_subject?>">
					</label>
					<p><?=__('use %subject% to insert a predefined subject', STATIC_MAKER_DEPLOY_EXTRA)?></p>
				</td>
			</tr>
		</table>

		<?php submit_button(__('Save', STATIC_MAKER_DEPLOY_EXTRA), 'primary', 'submit', true);?>

	</form>
</div>
