<?php
namespace Static_Maker\Deploy_Extra;

$table = new Deploy_List_Table(new Path(), new File(), new DB());
?>
<h1><?=__('Schedule List', STATIC_MAKER_DEPLOY_EXTRA)?></h3>

<div class="wrap">
	<h2 class="wp-heading-inline"><?=__('List', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
	<form method="post">
		<?php $table->prepare_items()?>
		<?php $table->search_box('search', 'search_id')?>
		<?php $table->display()?>
	</form>
</div>

<p><?=__('Now')?>: <?=date('Y-m-d H:i:s')?></p>
<ul>
	<?php foreach ($this->cron->get_cron_schedules() as $timestamp => $cron): ?>
		<li>
			<?=date('Y-m-d H:i:s', $timestamp)?>
			<button class="button smde-unschedule-deploy" data-timestamp="<?=$timestamp?>">削除</button>
		</li>
	<?php endforeach?>
</ul>
