<?php

declare(strict_types=1);

/**
 * @see HotspotAssetController::actionUpdate()
 *
 * @var View $this
 * @var HotspotAsset $asset
 */

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotHeader;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Media\Modules\Admin\Widgets\Forms\AssetActiveForm;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetActionDropdown;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;

echo HotspotHeader::make()
    ->hotspot($asset->model)
    ->content(AssetActionDropdown::make()
        ->model($asset));

echo HotspotSubmenu::make()
    ->hotspot($asset->model);

echo FormContainer::make()
    ->form(AssetActiveForm::make()
        ->model($asset));
