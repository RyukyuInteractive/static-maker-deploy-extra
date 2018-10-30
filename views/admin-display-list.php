<h3><?_e('Schedule List', STATIC_MAKER_DEPLOY_EXTRA)?></h3>
<p><?_e('Now')?>: <?=date('Y-m-d H:i:s')?></p>
<ul>
	<?foreach ($this->get_cron_schedules() as $timestamp => $cron): ?>
		<li>
			<?=date('Y-m-d H:i:s', $timestamp)?>
			<button class="button smde-unschedule-deploy" data-timestamp="<?=$timestamp?>">削除</button>
		</li>
	<?endforeach?>
</ul>
