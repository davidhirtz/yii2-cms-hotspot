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
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotHeader;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Media\Modules\Admin\Data\AssetArrayDataProvider;
use Hirtz\Media\Modules\Admin\Widgets\Grids\AssetGridView;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetModelActionDropdown;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;

echo HotspotHeader::make()
    ->hotspot($model)
    ->content(AssetModelActionDropdown::make()
        ->provider($provider));

echo HotspotSubmenu::make()
    ->hotspot($model);

echo GridContainer::make()
    ->grid(AssetGridView::make()
        ->provider($provider));
