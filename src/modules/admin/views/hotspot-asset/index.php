<?php
/**
 * @see HotspotAssetController::actionIndex()
 *
 * @var View $this
 * @var ActiveDataProvider $provider
 * @var Hotspot $hotspot
 */

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotAssetController;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\navs\HotspotSubmenu;
use davidhirtz\yii2\media\modules\admin\widgets\grids\FileGridView;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
use yii\data\ActiveDataProvider;

$this->setTitle(Yii::t('media', 'Assets'));
?>

<?= HotspotSubmenu::widget([
    'hotspot' => $hotspot,
]); ?>

<?php
$this->setBreadcrumb(Yii::t('cms', 'Link Assets'));
?>

<?= Panel::widget([
    'content' => FileGridView::widget([
        'dataProvider' => $provider,
        'parent' => $hotspot,
    ]),
]); ?>