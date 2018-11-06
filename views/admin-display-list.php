<?php
namespace Static_Maker\Deploy_Extra;

$table = new Deploy_List_Table(new Path(), new File(), new DB());
?>
<h1><?_e('Schedule List', STATIC_MAKER_DEPLOY_EXTRA)?></h3>

<div class="wrap">
	<h2 class="wp-heading-inline"><?_e('List', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
	<form method="post">
		<?$table->prepare_items()?>
		<?$table->search_box('search', 'search_id')?>
		<?$table->display()?>
	</form>
</div>

<p><?_e('Now')?>: <?=date('Y-m-d H:i:s')?></p>
<ul>
	<?foreach ($this->cron->get_cron_schedules() as $timestamp => $cron): ?>
		<li>
			<?=date('Y-m-d H:i:s', $timestamp)?>
			<button class="button smde-unschedule-deploy" data-timestamp="<?=$timestamp?>">削除</button>
		</li>
	<?endforeach?>
</ul>
