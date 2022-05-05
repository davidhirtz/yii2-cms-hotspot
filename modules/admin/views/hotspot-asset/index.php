<?php
/**
 * Asset file grid.
 * @see \davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotAssetController::actionIndex()
 *
 * @var View $this
 * @var ActiveDataProvider $provider
 * @var Hotspot $hotspot
 */

use davidhirtz\yii2\cms\modules\admin\widgets\nav\Submenu;
use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\media\modules\admin\widgets\grid\FileGridView;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
use yii\data\ActiveDataProvider;

$this->setTitle(Yii::t('media', 'Assets'));
?>

<?= Submenu::widget([
    'model' => $hotspot->asset,
]); ?>

<?php
$this->setBreadcrumbs([
    Yii::t('cms', 'Asset') => ['/admin/asset/update', 'id' => $hotspot->asset_id],
    Yii::t('hotspot', 'Hotspot') => ['/admin/hotspot/update', 'id' => $hotspot->id],
]);
?>

<?= Panel::widget([
    'content' => FileGridView::widget([
        'dataProvider' => $provider,
        'parent' => $hotspot,
    ]),
]); ?>