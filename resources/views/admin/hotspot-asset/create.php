<?php

declare(strict_types=1);

/**
 * @see HotspotAssetController::actionCreate()
 *
 * @var View $this
 * @var Hotspot $model
 * @var FileActiveDataProvider $provider
 * @var HotspotAsset|null $asset
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotHeader;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Media\Modules\Admin\Data\FileActiveDataProvider;
use Hirtz\Media\Modules\Admin\Widgets\Grids\FileGridView;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;

echo HotspotHeader::make()
    ->model($model);

echo HotspotSubmenu::make()
    ->model($model);

echo GridContainer::make()
    ->grid(FileGridView::make()
        ->provider($provider)
        ->model($model)
        ->asset($asset));
