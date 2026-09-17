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
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Cms\Modules\Admin\Widgets\Navs\FrontendLink;
use Hirtz\Media\Modules\Admin\Widgets\Forms\AssetActiveForm;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetActionDropdown;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetHeader;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;

echo AssetHeader::make()
    ->model($asset)
    ->subheading(FrontendLink::findInChain($asset)?->addClass('hidden-sticky'))
    ->content(AssetActionDropdown::make()
        ->model($asset));

echo HotspotSubmenu::make()
    ->model($asset->model);

echo FormContainer::make()
    ->form(AssetActiveForm::make()
        ->model($asset));
