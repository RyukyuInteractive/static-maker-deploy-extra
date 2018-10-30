<?php

$crypto_util = $this->static_maker->crypto_util;
$options = get_option(STATIC_MAKER_DEPLOY_EXTRA);

$remote_host = $options ? $options['remote_host'] ?? '' : '';
$remote_user = $options ? $options['remote_user'] ?? '' : '';
$remote_dir = $options ? $options['remote_dir'] ?? '' : '';
$remote_ssh_key = $options ? $options['remote_ssh_key'] ?? '' : '';

?>
<div class="wrap">

	<h2><?=esc_html(get_admin_page_title());?></h2>

	<?settings_errors();?>

	<form method="post" name="<?=STATIC_MAKER_DEPLOY_EXTRA?>" action="options.php">

		<?settings_fields(STATIC_MAKER_DEPLOY_EXTRA)?>
		<?do_settings_sections(STATIC_MAKER_DEPLOY_EXTRA);?>

		<table class="form-table">
			<tr>
				<th scope="row"><label
						for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-host'?>"><?_e('Remote Host', STATIC_MAKER_DEPLOY_EXTRA)?></label>
				</th>
				<td>
					<input type="text" class="regular-text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-host'?>"
						   name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[remote_host]"
						   placeholder="<?_e('ip, hostname etc..', STATIC_MAKER_DEPLOY_EXTRA)?>"
						   value="<?=$remote_host?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label
						for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-user'?>"><?_e('Remote User', STATIC_MAKER_DEPLOY_EXTRA)?></label>
				</th>
				<td>
					<input type="text" class="regular-text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-user'?>"
						   name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[remote_user]"
						   placeholder="<?_e('ec2-user, centos etc...', STATIC_MAKER_DEPLOY_EXTRA)?>"
						   value="<?=$remote_user?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label
						for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-dir'?>"><?_e('Remote Directory', STATIC_MAKER_DEPLOY_EXTRA)?></label>
				</th>
				<td>
					<input type="text" class="regular-text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-dir'?>"
						   name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[remote_dir]"
						   placeholder="<?_e('/var/www/html etc...', STATIC_MAKER_DEPLOY_EXTRA)?>"
						   value="<?=$remote_dir?>">
				</td>
			</tr>
			<tr>
				<th><?_e('SSH Private Key', STATIC_MAKER_DEPLOY_EXTRA)?></th>
				<td>
					<label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-ssh-key'?>">
						<textarea id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-ssh-key'?>" class="large-text code"
								  name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[remote_ssh_key]'?>"><?=$crypto_util::decrypt($remote_ssh_key, true)?></textarea>
					</label>
				</td>
			</tr>
		</table>

		<?php submit_button(__('Save', STATIC_MAKER_DEPLOY_EXTRA), 'primary', 'submit', true);?>

	</form>
</div>
