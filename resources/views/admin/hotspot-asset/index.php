<?php

declare(strict_types=1);

/**
 * @see HotspotAssetController::actionIndex()
 *
 * @var View $this
 * @var ActiveDataProvider $provider
 * @var Hotspot $hotspot
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Media\Modules\Admin\Widgets\Grids\FileGridView;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;
use yii\data\ActiveDataProvider;

$this->title(Yii::t('media', 'Assets'));

echo HotspotSubmenu::make()
    ->hotspot($hotspot);

$this->addBreadcrumb(Yii::t('cms', 'Link Assets'));

echo GridContainer::make()
    ->grid(FileGridView::make()
        ->parent($hotspot)
        ->provider($provider));
