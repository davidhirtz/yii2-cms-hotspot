<?php
declare(strict_types=1);

/**
 * @see HotspotController::actionUpdate()
 *
 * @var View $this
 * @var Hotspot $hotspot
 */

use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\hotspot\modules\admin\controllers\HotspotController;
use Hirtz\Cms\hotspot\modules\admin\widgets\forms\HotspotActiveForm;
use Hirtz\Cms\hotspot\modules\admin\widgets\grids\HotspotAssetGridView;
use Hirtz\Cms\hotspot\modules\admin\widgets\navs\HotspotSubmenu;
use Hirtz\Cms\hotspot\modules\admin\widgets\panels\HotspotHelpPanel;
use Hirtz\Skeleton\helpers\Html;
use Hirtz\Skeleton\web\View;
use Hirtz\Skeleton\widgets\bootstrap\Panel;
use Hirtz\Skeleton\widgets\forms\DeleteActiveForm;

$this->setTitle(Yii::t('hotspot', 'Edit Hotspot'));
?>

<?= HotspotSubmenu::widget([
    'hotspot' => $hotspot,
]); ?>

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

<?= HotspotHelpPanel::widget([
    'model' => $hotspot,
]); ?>

<?= Panel::widget([
    'id' => 'delete',
    'type' => 'danger',
    'title' => Yii::t('hotspot', 'Delete Hotspot'),
    'content' => DeleteActiveForm::widget([
        'model' => $hotspot,
    ]),
]); ?>
