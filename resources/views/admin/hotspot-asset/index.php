<?php

declare(strict_types=1);

/**
 * @see HotspotAssetController::actionIndex()
 *
 * @var View $this
 * @var Hotspot $model
 * @var AssetArrayDataProvider $provider
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\HotspotAssetGridView;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Media\Modules\Admin\Data\AssetArrayDataProvider;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;

$this->title(Yii::t('media', 'ASSET_MODEL_LABEL'));

echo HotspotSubmenu::make()
    ->hotspot($model);

echo GridContainer::make()
    ->grid(HotspotAssetGridView::make()
        ->provider($provider));
