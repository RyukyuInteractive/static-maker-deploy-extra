<div id="smde-deploy" class="wrap">
	<?php if ($this->option->is_configured()): ?>
	<h2><?=esc_html(get_admin_page_title());?></h2>

	<div class="smde-deploy-app"></div>
	<?php else: ?>
	<p><?=_e('Please set rsync information from Deploy Settings', STATIC_MAKER_DEPLOY_EXTRA)?></p>
	<?php endif?>
</div>
