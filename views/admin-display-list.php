<h1><?_e('Schedule List', STATIC_MAKER_DEPLOY_EXTRA)?></h3>

<div class="wrap">
<h2 class="wp-heading-inline"><?_e('List', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
<?
$table = new Static_Maker\Deploy_Extra\Deploy_List_Table();
$table->prepare_items();
$table->display();
?>
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
