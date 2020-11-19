<?php
namespace Static_Maker\Deploy_Extra;

if (!isset($_GET['deploy']) || !$_GET['deploy']) {
    exit('specify id');
}

$id = $_GET['deploy'];
$db = new DB();
$deploy = $db->fetch_deploy($id);
$table = new Deploy_Files_List_Table($db, $id);
$table->prepare_items();


if(get_option('timezone_string') == 'Asia/Tokyo'){
    $timestamp = strtotime($deploy['date']) + (9 * 60 * 60);
} else {
    $timestamp = strtotime($deploy['date']);
}
$date = date('Y/m/d H:i:s',$timestamp);
?>

<div id="smde-deploy">
    <h1><?=__('Deploy Detail', STATIC_MAKER_DEPLOY_EXTRA)?></h1>

    <div class="wrap">
        <h2 class="wp-heading-inline"><?=__('Deploy Info', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
        <p><?=__('Date', STATIC_MAKER_DEPLOY_EXTRA)?>: <?=$date?></p>
        <p><?=__('Timestamp', STATIC_MAKER_DEPLOY_EXTRA)?>: <?=$deploy['timestamp']?></p>
        <p><?=__('Deploy ID', STATIC_MAKER_DEPLOY_EXTRA)?>: <?=$deploy['id']?></p>
    </div>

    <?php if ($deploy['type'] === 'partial'): ?>
    <div class="wrap">
        <h2 class="wp-heading-inline"><?=__('Files', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
        <form method="post">
            <?php $table->search_box('serach', 'search_id')?>
            <?php $table->display()?>
        </form>
    </div>
    <?php endif?>

    <div class="smde-deploy-app" style="display: contents;"></div>
</div>
