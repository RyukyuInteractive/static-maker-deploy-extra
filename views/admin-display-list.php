<?php
namespace Static_Maker\Deploy_Extra;

$table = new Deploy_List_Table(new Path(), new File(), new DB());
?>
<div id="smde-deploy">

	<?php if ($this->option->is_configured()): ?>

	<h1><?=__('Schedule List', STATIC_MAKER_DEPLOY_EXTRA)?></h3>

	<div class="wrap">
		<h2 class="wp-heading-inline"><?=__('List', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
		<form method="post">
			<?php $table->prepare_items()?>
			<?php $table->search_box('search', 'search_id')?>
			<?php $table->display()?>
		</form>
	</div>

	<h2 class="queue-list-title"><?=__('Queue List', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
	<p><?=__('Now', STATIC_MAKER_DEPLOY_EXTRA)?>: <?=date('Y-m-d H:i:s')?></p>
	<ul>
		<?php if (!count($this->cron->get_cron_schedules())): ?>
			<li><?=__('There is no waiting deploy', STATIC_MAKER_DEPLOY_EXTRA)?></li>
		<?php endif?>
		<?php foreach ($this->cron->get_cron_schedules() as $timestamp => $cron): ?>
			<li>
				<?=date('Y-m-d H:i:s', $timestamp)?>
				<button class="button smde-unschedule-deploy" data-timestamp="<?=$timestamp?>">削除</button>
			</li>
		<?php endforeach?>
	</ul>

	<?php else: ?>

	<p><?=_e('Please set rsync information from Deploy Settings', STATIC_MAKER_DEPLOY_EXTRA)?></p>

	<?php endif?>
</div>
