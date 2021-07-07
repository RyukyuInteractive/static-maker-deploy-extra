<?php

$crypto_util = $this->static_maker->crypto_util;
$options = get_option(STATIC_MAKER_DEPLOY_EXTRA);

//set configure form item value
$remote_host = $options ? $options['remote_host'] ?? '' : '';
$remote_user = $options ? $options['remote_user'] ?? '' : '';
$remote_dir = $options ? $options['remote_dir'] ?? '' : '';
$remote_ssh_key = $options ? $options['remote_ssh_key'] ?? '' : '';
$remote_ssh_key_path = $options ? $options['remote_ssh_key_path'] ?? '' : '';
$deploy_notify_email = $options ? $options['deploy_notify_email'] ?? '' : '';
$deploy_notify_email_subject = $options ? $options['deploy_notify_email_subject'] ?? '' : '';
$deploy_data_delete_days = $options ? $options['deploy_data_delete_days'] ?? '180' : '';
$s3_bucket = $options ? $options['s3_bucket'] ?? '' : '';
$s3_region = $options ? $options['s3_region'] ?? '' : '';
$s3_bucket_source = $options ? $options['s3_bucket_source'] ?? '' : '';
$s3_sync_option = $options ? $options['s3_sync_option'] ?? '' : '';
$deploy_type = $options ? $options['deploy_type'] ?? '' : '';
$is_clear_cache = $options ? $options['is_clear_cache'] ?? '' : 1;
$distribution_id = $options ? $options['distribution_id'] ?? '' : '';
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
                        for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-host'?>"><?=__('Deploy Type', STATIC_MAKER_DEPLOY_EXTRA)?></label>
                </th>
                <td>
                    <label style="padding-right: 10px">
                        <input type="radio" name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[deploy_type]" value="rsync" <?php if($deploy_type === 'rsync') echo 'checked="checked"'; ?> >Rsync
                    </label>
                    <label>
                        <input type="radio" name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[deploy_type]" value="s3" <?php if($deploy_type === 's3') echo 'checked="checked"'; ?>>AWS s3
                    </label>
                </td>
            </tr>
        </table>

        <h3><?=__('[Rsync]', STATIC_MAKER_DEPLOY_EXTRA)?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label
						for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-host'?>"><?=__('Remote Host', STATIC_MAKER_DEPLOY_EXTRA)?></label>
				</th>
				<td>
					<input type="text" class="regular-text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-host'?>"
						   name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[remote_host]"
						   placeholder="<?=__('ip, hostname etc..', STATIC_MAKER_DEPLOY_EXTRA)?>"
						   value="<?=esc_html($remote_host);?>">
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
						   value="<?=esc_html($remote_user);?>">
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
						   value="<?=esc_html($remote_dir);?>">
				</td>
			</tr>
			<tr>
				 <th scope="row"><label
						for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-ssh-key-path'?>"><?=__('SSH Private Key Path', STATIC_MAKER_DEPLOY_EXTRA)?></label>
				 </th>
				 <td>
					<input type="text" class="large-text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-remote-ssh-key-path'?>"
							name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[remote_ssh_key_path]"
							value="<?=$crypto_util::decrypt($remote_ssh_key_path, true)?>">
					<span>※SSH Private Keyが入力されている場合はSSH Private Keyの設定が優先されます。</span>
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
								  name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[deploy_notify_email]'?>" placeholder="admin@example.com" value="<?=esc_html($deploy_notify_email);?>">
					</label>
					<p><?=__('if you specify multiple emails, use comma to separate them', STATIC_MAKER_DEPLOY_EXTRA)?></p>
				</td>
			</tr>
			<tr>
				<th><?=__('Notification Subject', STATIC_MAKER_DEPLOY_EXTRA)?></th>
				<td>
					<label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-deploy-notify-email-subject'?>">
						<input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-deploy-notify-email-subject'?>" class="large-text code"
								  name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[deploy_notify_email_subject]'?>" placeholder="" value="<?=esc_html($deploy_notify_email_subject);?>">
					</label>
					<p><?=__('use %subject% to insert a predefined subject', STATIC_MAKER_DEPLOY_EXTRA)?></p>
				</td>
			</tr>
		</table>

        <h3><?=__('[AWS S3]', STATIC_MAKER_DEPLOY_EXTRA)?></h3>
        <table class="form-table">
            <tr>
                <th><?=__('Bucket', STATIC_MAKER_DEPLOY_EXTRA)?></th>
                <td>
                    <label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-s3_bucket'?>">
                        <input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-s3_bucket'?>" class="regular-text code"
                               name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[s3_bucket]'?>" placeholder="" value="<?=esc_html($s3_bucket)?>">
                    </label>
                </td>
            </tr>
            <tr>
                <th><?=__('Region', STATIC_MAKER_DEPLOY_EXTRA)?></th>
                <td>
                    <label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-s3_region'?>">
                        <input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-s3_region'?>" class="regular-text code"
                               name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[s3_region]'?>" placeholder="" value="<?=esc_html($s3_region)?>">
                    </label>
                </td>
            </tr>
            <tr>
                <th><?=__('S3 Bucket Path', STATIC_MAKER_DEPLOY_EXTRA)?></th>
                <td>
                    <label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-s3_bucket_source'?>">
                        <input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-s3_bucket_source'?>" class="regular-text code"
                               name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[s3_bucket_source]'?>" placeholder="" value="<?=esc_html($s3_bucket_source);?>">
                    </label>
                </td>
            </tr>
            <tr>
                <th><?=__('S3 Sync Option', STATIC_MAKER_DEPLOY_EXTRA)?></th>
                <td>
                    <label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-s3_sync_option'?>">
                        <input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-s3_sync_option'?>" class="large-text code"
                               name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[s3_sync_option]'?>" placeholder="" value="<?=esc_html($s3_sync_option);?>">
                    </label>
                </td>
            </tr>
        </table>



        <h3><?=__('[Option]', STATIC_MAKER_DEPLOY_EXTRA)?></h3>
        <table class="form-table">
            <tr>
                <th><?=__('Deployment data retention period', STATIC_MAKER_DEPLOY_EXTRA)?></th>
                <td>
                    <label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-deploy-data-delete-days'?>">
                        <input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-deploy-data-delete-days'?>" size="3"
                               name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[deploy_data_delete_days]'?>" placeholder="" value="<?=esc_html($deploy_data_delete_days);?>">
                        <?=__('day', STATIC_MAKER_DEPLOY_EXTRA)?>
                    </label>
                </td>
            </tr>
        </table>

        <h3><?=__('[CloudFront]', STATIC_MAKER_DEPLOY_EXTRA)?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-is-clear-cache'?>">
                        <?=__('Is clear cache', STATIC_MAKER_DEPLOY_EXTRA)?>
                    </label>
                </th>
                <td>
                    <label style="padding-right: 10px">
                        <input type="checkbox" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-is-clear-cache'?>" name="<?=STATIC_MAKER_DEPLOY_EXTRA?>[is_clear_cache]" value="1" <?php checked($is_clear_cache, 1)?>>
                        <?php _e('Clear cache', STATIC_MAKER_DEPLOY_EXTRA)?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="<?=STATIC_MAKER_DEPLOY_EXTRA . '-distribution-id'?>">
                        <?=__('Distribution ID', STATIC_MAKER_DEPLOY_EXTRA)?>
                    </label>
                </th>
                <td>
                    <input type="text" id="<?=STATIC_MAKER_DEPLOY_EXTRA . '-distribution-id'?>" class="regular-text code"
                           name="<?=STATIC_MAKER_DEPLOY_EXTRA . '[distribution_id]'?>" placeholder="" value="<?=esc_html($distribution_id)?>">
                </td>
            </tr>
        </table>

		<?php submit_button(__('Save', STATIC_MAKER_DEPLOY_EXTRA), 'primary', 'submit', true);?>

	</form>
</div>
