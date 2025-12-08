<?php
declare(strict_types=1);

/**
 * @see HotspotAssetController::actionIndex()
 *
 * @var View $this
 * @var ActiveDataProvider $provider
 * @var Hotspot $hotspot
 */

use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\hotspot\modules\admin\controllers\HotspotAssetController;
use Hirtz\Cms\hotspot\modules\admin\widgets\navs\HotspotSubmenu;
use Hirtz\Media\modules\admin\widgets\grids\FileGridView;
use Hirtz\Skeleton\web\View;
use Hirtz\Skeleton\widgets\bootstrap\Panel;
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
