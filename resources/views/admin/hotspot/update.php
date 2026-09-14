<?php

declare(strict_types=1);

/**
 * @see HotspotController::actionUpdate()
 *
 * @var View $this
 * @var Hotspot $hotspot
 */

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotController;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms\HotspotActiveForm;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotActionDropdown;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotHeader;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;

echo HotspotHeader::make()
    ->hotspot($hotspot)
    ->content(HotspotActionDropdown::make()
        ->model($hotspot));

echo HotspotSubmenu::make()
    ->hotspot($hotspot);

echo FormContainer::make()
    ->form(HotspotActiveForm::make()
        ->model($hotspot));
