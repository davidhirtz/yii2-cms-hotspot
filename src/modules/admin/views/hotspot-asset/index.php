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
use Hirtz\Cms\hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Media\Modules\Admin\Widgets\Grids\FileGridView;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Bootstrap\Panel;
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
