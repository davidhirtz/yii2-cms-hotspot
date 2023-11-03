<?php
/**
 * Update hotspot.
 * @see \davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotController::actionUpdate()
 *
 * @var View $this
 * @var Hotspot $hotspot
 */

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms\HotspotActiveForm;
use davidhirtz\yii2\cms\modules\admin\widgets\navs\Submenu;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids\HotspotAssetGridView;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
use davidhirtz\yii2\skeleton\widgets\forms\DeleteActiveForm;

$this->setTitle(Yii::t('hotspot', 'Edit Hotspot'));
?>

<?= Submenu::widget([
    'model' => $hotspot->asset,
]); ?>

<?php
$this->setBreadcrumb(Yii::t('cms', 'Asset'), ['/admin/asset/update', 'id' => $hotspot->asset_id]);
?>

<?= Html::errorSummary($hotspot); ?>

<?= Panel::widget([
    'title' => $this->title,
    'content' => HotspotActiveForm::widget([
        'model' => $hotspot,
    ]),

]); ?>

<?php if ($hotspot->hasAssetsEnabled()) {
    echo Panel::widget([
        'id' => 'assets',
        'title' => Yii::t('cms', 'Assets'),
        'content' => HotspotAssetGridView::widget([
            'parent' => $hotspot,
        ]),
    ]);
} ?>

<?= Panel::widget([
    'id' => 'delete',
    'type' => 'danger',
    'title' => Yii::t('hotspot', 'Delete Hotspot'),
    'content' => DeleteActiveForm::widget([
        'model' => $hotspot,
    ]),
]); ?>
