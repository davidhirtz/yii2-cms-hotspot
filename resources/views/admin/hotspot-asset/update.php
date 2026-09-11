<?php

declare(strict_types=1);

/**
 * @see AbstractAssetController::actionUpdate()
 *
 * @var View $this
 * @var HotspotAsset $asset
 */

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Media\Modules\Admin\Controllers\AbstractAssetController;
use Hirtz\Media\Modules\Admin\Widgets\Forms\AssetActiveForm;
use Hirtz\Media\Modules\Admin\Widgets\Navs\AssetActionDropdown;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;

$this->title($asset->getTrailModelName());

echo HotspotSubmenu::make()
    ->hotspot($asset->model)
    ->content(AssetActionDropdown::make()
        ->model($asset));

echo FormContainer::make()
    ->title($this->title)
    ->form(AssetActiveForm::make()
        ->model($asset));
