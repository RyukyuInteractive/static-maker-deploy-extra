<div class="wrap">
	<h2><?=esc_html(get_admin_page_title());?></h2>

	<h3><?=__('Schedule', STATIC_MAKER_DEPLOY_EXTRA)?></h3>

	<div class="deploy-type-buttons"></div>
	<div class="diff-actions" style="display: contents;"></div>
	<div class="diff-table-output" style="display: contents;"></div>
	<div class="diff-confirm-output" style="display: contents;"></div>
	<div class="smde-deploy-form-wrapper" style="display: contents;"></div>

	<div class="smde-deploy-app"></div>

	<h3><?=__('Schedule List', STATIC_MAKER_DEPLOY_EXTRA)?></h3>
	<p><?=__('Now')?>: <?=date('Y-m-d H:i:s')?></p>
	<ul>
		<?php foreach ($this->cron->get_cron_schedules() as $timestamp => $cron): ?>
			<li>
				<?=date('Y-m-d H:i:s', $timestamp)?>
				<button class="button smde-unschedule-deploy" data-timestamp="<?=$timestamp?>">削除</button>
			</li>
		<?php endforeach?>
	</ul>
</div>
