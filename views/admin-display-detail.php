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
?>

<h1><?_e('Deploy Detail', STATIC_MAKER_DEPLOY_EXTRA)?></h1>

<div class="wrap">
    <h2 class="wp-heading-inline"><?_e('Deploy Info', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
    <p><?_e('Date', STATIC_MAKER_DEPLOY_EXTRA)?>: <?=$deploy['date']?></p>
    <p><?_e('Timestamp', STATIC_MAKER_DEPLOY_EXTRA)?>: <?=$deploy['timestamp']?></p>
    <p><?_e('Deploy ID', STATIC_MAKER_DEPLOY_EXTRA)?>: <?=$deploy['id']?></p>
</div>

<?if ($deploy['type'] === 'partial'): ?>
<div class="wrap">
    <h2 class="wp-heading-inline"><?_e('Files', STATIC_MAKER_DEPLOY_EXTRA)?></h2>
    <form method="post">
        <?$table->search_box('serach', 'search_id')?>
        <?$table->display()?>
    </form>
</div>
<?endif?>

<div class="smde-deploy-action-wrapper" style="display: contents;"></div>
