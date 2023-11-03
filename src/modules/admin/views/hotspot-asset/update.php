<?php
/**
 * @see HotspotAssetController::actionUpdate()
 *
 * @var View $this
 * @var HotspotAsset $asset
 */

use davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotAssetController;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms\HotspotAssetActiveForm;
use davidhirtz\yii2\cms\modules\admin\widgets\navs\Submenu;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
use davidhirtz\yii2\skeleton\widgets\forms\DeleteActiveForm;

$this->setTitle(Yii::t('hotspot', 'Edit Hotspot Asset'));
?>

<?= Submenu::widget([
    'model' => $asset->hotspot->asset,
]); ?>

<?php
$this->setBreadcrumbs([
    Yii::t('cms', 'Asset') => ['/admin/asset/update', 'id' => $asset->hotspot->asset_id],
    Yii::t('hotspot', 'Hotspot') => ['/admin/hotspot/update', 'id' => $asset->hotspot->id],
    Yii::t('media', 'Assets') =>  ['/admin/hotspot/update', 'id' => $asset->hotspot->id, '#' => 'assets'],
]);
?>

<?= Html::errorSummary($asset); ?>

<?= Panel::widget([
    'title' => $this->title,
    'content' => HotspotAssetActiveForm::widget([
        'model' => $asset,
    ]),
]); ?>

<?= Panel::widget([
        'type' => 'danger',
        'title' => Yii::t('cms', 'Remove Asset'),
        'content' => DeleteActiveForm::widget([
            'model' => $asset,
            'buttons' => Html::button(Yii::t('cms', 'Remove'), [
                'class' => 'btn btn-danger',
                'data-method' => 'post',
                'data-message' => Yii::t('cms', 'Are you sure you want to remove this asset?'),
                'type' => 'submit',
            ]),
            'message' => Yii::t('cms', 'Notice: Removing an asset will not delete the actual file.')
        ]),
    ]); ?>

<?php if (Yii::$app->getUser()->can('fileDelete', ['file' => $asset->file])) {
    echo Panel::widget([
        'type' => 'danger',
        'title' => Yii::t('media', 'Delete File'),
        'content' => DeleteActiveForm::widget([
            'model' => $asset->file,
            'action' => ['/admin/file/delete', 'id' => $asset->file_id],
            'message' => Yii::t('cms', 'Warning: Deleting this file cannot be undone. All related assets will also be unrecoverably deleted. Please be certain!')
        ]),
    ]);
} ?>