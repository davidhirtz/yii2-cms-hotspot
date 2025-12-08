<?php
declare(strict_types=1);

/**
 * @see HotspotController::actionUpdate()
 *
 * @var View $this
 * @var Hotspot $hotspot
 */

use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\hotspot\Modules\Admin\Controllers\HotspotController;
use Hirtz\Cms\hotspot\Modules\Admin\Widgets\Forms\HotspotActiveForm;
use Hirtz\Cms\hotspot\Modules\Admin\Widgets\Grids\HotspotAssetGridView;
use Hirtz\Cms\hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Cms\hotspot\Modules\Admin\Widgets\Panels\HotspotHelpPanel;
use Hirtz\Skeleton\Helpers\Html;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Bootstrap\Panel;
use Hirtz\Skeleton\Widgets\Forms\DeleteActiveForm;

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
